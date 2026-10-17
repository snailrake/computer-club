<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
{
    public function test_two_processes_cannot_reserve_the_same_period(): void
    {
        $directory = storage_path('framework/testing/concurrency-'.bin2hex(random_bytes(6)));
        mkdir($directory, 0777, true);
        $database = $directory.'/database.sqlite';
        touch($database);
        config(['database.connections.sqlite.database' => $database]);
        DB::purge('sqlite');
        $first = $second = null;

        try {
            $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
            $user = User::factory()->create();
            $computer = Computer::query()->create(['name' => 'PC-RACE', 'is_active' => true]);
            $day = now()->addDay()->format('Y-m-d');
            $arguments = [PHP_BINARY, base_path('tests/Fixtures/booking-worker.php'), $database,
                (string) $user->id, (string) $computer->id, $day.'T10:00', $day.'T11:00', $directory];
            $environment = ['APP_ENV' => 'testing', 'DB_DATABASE' => $database, 'CACHE_STORE' => 'array'];
            $first = new Process([...$arguments, 'first'], base_path(), $environment, timeout: 30);
            $second = new Process([...$arguments, 'second'], base_path(), $environment, timeout: 30);

            $first->start();
            $deadline = microtime(true) + 20;

            while (! is_file($directory.'/first-ready') && $first->isRunning() && microtime(true) < $deadline) {
                usleep(20_000);
            }

            $this->assertFileExists($directory.'/first-ready', $first->getErrorOutput().$first->getOutput());
            $second->start();
            $first->wait();
            $second->wait();

            $this->assertTrue($first->isSuccessful(), $first->getErrorOutput().$first->getOutput());
            $this->assertTrue($second->isSuccessful(), $second->getErrorOutput().$second->getOutput());
            $firstResult = json_decode($first->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            $secondResult = json_decode($second->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('saved', $firstResult['result']);
            $this->assertSame('conflict', $secondResult['result']);
            $this->assertGreaterThanOrEqual(0.2, $secondResult['seconds']);
            $this->assertSame(1, Booking::query()->count());
        } finally {
            $first?->stop();
            $second?->stop();
            DB::purge('sqlite');

            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }

            rmdir($directory);
        }
    }
}
