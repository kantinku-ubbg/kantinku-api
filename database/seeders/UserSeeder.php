<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Admin Kantin', 'email' => 'admin@kantinku.test', 'role' => User::ROLE_ADMIN, 'phone' => '081234567890'],
            ['name' => 'Miftahul Jannah', 'email' => 'miftahul@kantinku.test', 'role' => User::ROLE_ADMIN, 'phone' => '081234567891'],
            ['name' => 'Susilawati', 'email' => 'susilawati@kantinku.test', 'role' => User::ROLE_CUSTOMER, 'phone' => '081234567892'],
            ['name' => 'Fazil Ikhram', 'email' => 'fazil@kantinku.test', 'role' => User::ROLE_CUSTOMER, 'phone' => '081234567893'],
            ['name' => 'Arzan Amin', 'email' => 'arzan@kantinku.test', 'role' => User::ROLE_CUSTOMER, 'phone' => '081234567894'],
            ['name' => 'Mirza Firdaus', 'email' => 'mirza@kantinku.test', 'role' => User::ROLE_CUSTOMER, 'phone' => '081234567895'],
        ];

        foreach ($rows as $row) {
            User::updateOrCreate(
                ['email' => $row['email']],
                $row + ['password' => 'password123'],
            );
        }
    }
}
