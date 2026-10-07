<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'instructor', 'business', 'student'] as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.test'],
            ['name' => 'Admin', 'password' => Hash::make('change-me-now'), 'email_verified_at' => now()],
        );
        $admin->assignRole('admin');
    }
}
