<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\PatientProfile;
use App\Models\TherapistProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds to populate development test accounts.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->info('DevSeeder skipped in production environment.');
            return;
        }

        // 1. Ensure Roles are seeded
        $this->call(RoleSeeder::class);

        // 2. Landlord User
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

        // 3. Primary Center
        $center = Center::firstOrCreate(
            ['name' => 'Massar Physical Therapy Center'],
            [
                'phone' => '+201011110000',
                'email' => 'contact@massarcenter.com',
                'specialty' => 'Physical Therapy & Rehabilitation',
                'country' => 'Egypt',
                'city' => 'Cairo',
                'therapists_count' => 5,
                'branches_count' => 1,
                'status' => 'active',
                'subscription_status' => 'active',
            ]
        );

        // 4. Center Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@massar.com'],
            [
                'center_id' => $center->id,
                'name' => 'Center Admin',
                'phone' => '+201011111111',
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // 5. Center Therapist User
        $therapist = User::firstOrCreate(
            ['email' => 'therapist@massar.com'],
            [
                'center_id' => $center->id,
                'name' => 'Dr. Ahmed Specialist',
                'phone' => '+201022222222',
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]
        );
        if (! $therapist->hasRole('therapist')) {
            $therapist->assignRole('therapist');
        }

        TherapistProfile::firstOrCreate(
            ['user_id' => $therapist->id],
            [
                'specialization' => 'Orthopedic Physical Therapy',
                'bio' => 'Experienced physical therapist specializing in spine rehabilitation.',
            ]
        );

        // 6. Patient User
        $patient = User::firstOrCreate(
            ['email' => 'patient@massar.com'],
            [
                'center_id' => $center->id,
                'name' => 'Mohamed Patient',
                'phone' => '+201033333333',
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]
        );
        if (! $patient->hasRole('patient')) {
            $patient->assignRole('patient');
        }

        PatientProfile::firstOrCreate(
            ['user_id' => $patient->id],
            [
                'center_id' => $center->id,
                'therapist_id' => $therapist->id,
                'birth_date' => '1995-05-15',
                'current_week' => 4,
                'patient_history' => 'No major surgeries.',
                'chief_complain' => 'Lower back stiffness and pain.',
                'diagnosis' => 'Lumbar Muscle Strain',
                'special_tests_notes' => 'Straight leg raise negative.',
                'objective_findings' => 'Limited lumbar flexion.',
            ]
        );
    }
}
