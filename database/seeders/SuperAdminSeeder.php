<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'frahmat68@gmail.com');
        $name = env('SUPER_ADMIN_NAME', 'Fikri Rachmat');
        $password = env('SUPERADMIN_PASSWORD', env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!'));

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
