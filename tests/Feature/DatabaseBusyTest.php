<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use Tests\TestCase;

class DatabaseBusyTest extends TestCase
{
    public function test_database_busy_error_has_a_readable_response_without_sql_details(): void
    {
        Route::get('/test-database-busy', function (): never {
            $cause = new PDOException('database is locked');
            $cause->errorInfo = ['HY000', 5, 'database is locked'];

            throw new QueryException('sqlite', 'private SQL statement', [], $cause);
        });

        $this->get('/test-database-busy')->assertStatus(503)->assertHeader('Retry-After', '1')
            ->assertSee('Повторите попытку чуть позже')->assertDontSee('private SQL statement');
    }

    public function test_real_lock_at_transaction_start_also_returns_a_readable_503(): void
    {
        $database = storage_path('framework/testing/busy-'.bin2hex(random_bytes(6)).'.sqlite');

        if (! is_dir(dirname($database))) {
            mkdir(dirname($database), 0777, true);
        }

        touch($database);
        config(['database.connections.sqlite.database' => $database, 'database.connections.sqlite.busy_timeout' => 20]);
        DB::purge('sqlite');
        $lockingConnection = new PDO('sqlite:'.$database);
        $lockingConnection->exec('BEGIN IMMEDIATE TRANSACTION');
        Route::get('/test-transaction-busy', fn () => DB::transaction(fn () => response('saved')));

        try {
            $this->get('/test-transaction-busy')->assertStatus(503)->assertHeader('Retry-After', '1')
                ->assertSee('Повторите попытку чуть позже')->assertDontSee('BEGIN IMMEDIATE');
        } finally {
            $lockingConnection->exec('ROLLBACK');
            unset($lockingConnection);
            DB::purge('sqlite');
            unlink($database);
        }
    }
}
