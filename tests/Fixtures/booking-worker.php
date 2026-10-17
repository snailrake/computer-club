<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
[, , $userId, $computerId, $start, $end, $directory, $worker] = $argv;
$booking = new Booking();
$booking->user()->associate(User::query()->findOrFail((int) $userId));

if ($worker === 'first') {
    DB::listen(function (QueryExecuted $query) use ($directory): void {
        if (! str_contains($query->sql, 'from "bookings"') || is_file($directory.'/first-ready')) {
            return;
        }

        touch($directory.'/first-ready');
        $deadline = microtime(true) + 20;

        while (! is_file($directory.'/second-ready')) {
            if (microtime(true) >= $deadline) {
                throw new RuntimeException('Second worker did not arrive.');
            }

            usleep(20_000);
        }

        usleep(300_000);
    });
}

$started = microtime(true);

if ($worker === 'second') {
    touch($directory.'/second-ready');
}

try {
    $app->make(BookingService::class)->save($booking, (int) $computerId, $start, $end);
    $result = 'saved';
} catch (ValidationException $exception) {
    if (! isset($exception->errors()['starts_at'])) {
        throw $exception;
    }

    $result = 'conflict';
}

echo json_encode(['result' => $result, 'seconds' => microtime(true) - $started], JSON_THROW_ON_ERROR);
