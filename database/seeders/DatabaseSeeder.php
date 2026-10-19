<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $accounts = [
            ['name' => 'Администратор', 'email' => 'admin@club.test', 'role' => UserRole::Admin],
            ['name' => 'Алексей', 'email' => 'client@club.test', 'role' => UserRole::Client],
            ['name' => 'Мария', 'email' => 'second@club.test', 'role' => UserRole::Client],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrNew(['email' => $account['email']]);

            if (! $user->exists) {
                $user->forceFill([...$account, 'password' => '123'])->save();
            }
        }

        foreach ([
            ['name' => 'PC-01', 'description' => 'Intel Core i5 · GeForce RTX 4060 · 16 ГБ RAM', 'is_active' => true],
            ['name' => 'PC-02', 'description' => 'AMD Ryzen 5 · GeForce RTX 4070 · 32 ГБ RAM', 'is_active' => true],
            ['name' => 'PC-03', 'description' => 'Техническое обслуживание. Временно не принимает новые брони.', 'is_active' => false],
        ] as $computer) {
            Computer::query()->firstOrCreate(['name' => $computer['name']], $computer);
        }
    }
}
