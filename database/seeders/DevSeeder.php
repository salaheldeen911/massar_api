<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\Exercise;
use App\Models\NutritionPlan;
use App\Models\PatientExercise;
use App\Models\PatientProfile;
use App\Models\TherapistProfile;
use App\Models\TreatmentPlan;
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

        // 1. Ensure Roles & Landlord are seeded
        $this->call([
            RoleSeeder::class,
            LandlordSeeder::class,
        ]);

        // 2. Primary Center
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

        // 3. Center Admin User
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

        // 4. Center Therapist User
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

        // 5. Patient User (Belongs to Center AND Assigned to Dr. Ahmed)
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

        // 6. Sample Treatment Plan for Patient
        TreatmentPlan::firstOrCreate(
            ['patient_id' => $patient->id],
            [
                'therapist_id' => $therapist->id,
                'manual_therapy' => 'Spine mobilization and soft tissue release twice weekly.',
                'electrotherapy' => 'TENS application for 20 minutes.',
                'medications' => 'Anti-inflammatory as prescribed by doctor.',
                'goals' => 'Achieve full pain-free lumbar range of motion in 4 weeks.',
            ]
        );

        // 7. Sample Nutrition Plan for Patient
        NutritionPlan::firstOrCreate(
            ['patient_id' => $patient->id],
            [
                'therapist_id' => $therapist->id,
                'breakfast' => 'Oatmeal with honey and boiled eggs.',
                'lunch' => 'Grilled chicken breast with green salad and brown rice.',
                'dinner' => 'Greek yogurt with almonds.',
                'snacks' => 'Fresh fruit and green tea.',
                'supplements' => 'Omega-3 and Vitamin D3.',
                'foods_to_avoid' => 'Refined sugars and fried foods.',
            ]
        );

        // 8. Sample Assigned Exercise for Patient
        $systemExercise = Exercise::withoutGlobalScope('center_scope')->first();
        if ($systemExercise) {
            PatientExercise::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'exercise_id' => $systemExercise->id,
                ],
                [
                    'assigned_by' => $therapist->id,
                    'sets' => 3,
                    'repeats' => 12,
                    'duration' => 90,
                    'notes' => 'Perform slowly every morning.',
                    'status' => 'pending',
                ]
            );
        }
    }
}
