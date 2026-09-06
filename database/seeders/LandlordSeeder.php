<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LandlordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $landlord = User::firstOrCreate(
            ['email' => 'landlord@massar.com'],
            [
                'name' => 'Massar Landlord Admin',
                'phone' => '+201000000000',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'center_id' => null,
            ]
        );

        if (! $landlord->hasRole('landlord')) {
            $landlord->assignRole('landlord');
        }
    }
}
