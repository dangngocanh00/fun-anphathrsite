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
            ['name' => 'Admin AnPhat',     'email' => 'admin@anphat.test',      'role' => 'admin',      'ref_code' => null],
            ['name' => 'HR Manager 1',     'email' => 'manager1@anphat.test',   'role' => 'hr_manager', 'ref_code' => 'manager1'],
            ['name' => 'HR Manager 2',     'email' => 'manager2@anphat.test',   'role' => 'hr_manager', 'ref_code' => 'manager2'],
            ['name' => 'HR Staff 1',       'email' => 'hr1@anphat.test',        'role' => 'hr',         'ref_code' => 'hr01'],
            ['name' => 'HR Staff 2',       'email' => 'hr2@anphat.test',        'role' => 'hr',         'ref_code' => 'hr02'],
            ['name' => 'HR Staff 3',       'email' => 'hr3@anphat.test',        'role' => 'hr',         'ref_code' => 'hr03'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'ref_code' => $data['ref_code'],
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
