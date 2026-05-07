<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin AnPhat',     'email' => 'admin@anphat.test',      'role' => 'admin'],
            ['name' => 'HR Manager 1',     'email' => 'manager1@anphat.test',   'role' => 'hr_manager'],
            ['name' => 'HR Manager 2',     'email' => 'manager2@anphat.test',   'role' => 'hr_manager'],
            ['name' => 'HR Staff 1',       'email' => 'hr1@anphat.test',        'role' => 'hr'],
            ['name' => 'HR Staff 2',       'email' => 'hr2@anphat.test',        'role' => 'hr'],
            ['name' => 'HR Staff 3',       'email' => 'hr3@anphat.test',        'role' => 'hr'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
