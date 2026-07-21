<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $stylist = Staff::create([
            'name' => 'Sanduni Perera',
            'role_title' => 'Hair Stylist',
            'phone' => '0771234567',
            'commission_percent' => 20,
        ]);

        Staff::create([
            'name' => 'Dilhani Fernando',
            'role_title' => 'Makeup Artist',
            'phone' => '0772345678',
            'commission_percent' => 25,
        ]);

        Staff::create([
            'name' => 'Kavindi Silva',
            'role_title' => 'Nail Technician',
            'phone' => '0773456789',
            'commission_percent' => 15,
        ]);

        // A demo staff-role login, linked to Sanduni's profile, for testing
        // the Jobs/Customers area with staff-level (not admin) access.
        User::updateOrCreate(
            ['email' => 'staff@yoursalon.com'],
            [
                'name' => $stylist->name,
                'password' => Hash::make('password'),
                'is_admin' => false,
                'role' => 'staff',
                'staff_id' => $stylist->id,
            ]
        );
    }
}
