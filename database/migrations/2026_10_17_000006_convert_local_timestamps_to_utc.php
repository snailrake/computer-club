<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {

        $this->convertTimestamps('Asia/Baku', 'UTC');
    }

    public function down(): void
    {
        $this->convertTimestamps('UTC', 'Asia/Baku');
    }

    private function convertTimestamps(string $fromTimezone, string $toTimezone): void
    {
        $tables = [
            'users' => ['created_at', 'updated_at', 'email_verified_at'],
            'computers' => ['created_at', 'updated_at'],
            'bookings' => ['starts_at', 'ends_at', 'created_at', 'updated_at'],
        ];

        foreach ($tables as $table => $columns) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns, $fromTimezone, $toTimezone): void {
                foreach ($rows as $row) {
                    $timestamps = [];

                    foreach ($columns as $column) {
                        if ($row->{$column} !== null) {
                            $timestamps[$column] = CarbonImmutable::parse($row->{$column}, $fromTimezone)
                                ->setTimezone($toTimezone)->format('Y-m-d H:i:s');
                        }
                    }

                    if ($timestamps !== []) {
                        DB::table($table)->where('id', $row->id)->update($timestamps);
                    }
                }
            });
        }
    }
};
