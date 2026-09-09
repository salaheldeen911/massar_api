<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Center;
use App\Models\ChatMessage;
use App\Models\ClinicalDirector;
use App\Models\Exercise;
use App\Models\NutritionPlan;
use App\Models\PatientExercise;
use App\Models\PatientExerciseLog;
use App\Models\PatientProfile;
use App\Models\SupportTicket;
use App\Models\Testimonial;
use App\Models\TherapistProfile;
use App\Models\TreatmentPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_14_schema_tables_and_models_work_correctly(): void
    {
        $this->seed(RoleSeeder::class);

        // 1. Center
        $center = Center::create([
            'name' => 'Massar Medical Center',
            'phone' => '+201000000000',
            'specialty' => 'Physical Therapy',
            'city' => 'Cairo',
            'status' => 'active',
            'facebook' => 'https://facebook.com/center',
            'whatsapp' => '+201000000000',
        ]);
        $this->assertDatabaseHas('centers', ['id' => $center->id]);

        // 2. Users (Therapist & Patient)
        $therapistUser = User::create([
            'center_id' => $center->id,
            'name' => 'Dr. Ahmed',
            'phone' => '+201011111111',
            'email' => 'dr.ahmed@massar.com',
            'password' => bcrypt('password'),
            'avatar' => 'avatars/therapist.png',
        ]);
        $therapistUser->assignRole('therapist');

        $patientUser = User::create([
            'center_id' => $center->id,
            'name' => 'Mohamed Ali',
            'phone' => '+201022222222',
            'email' => 'm.ali@patient.com',
            'password' => bcrypt('password'),
            'avatar' => 'avatars/patient.png',
        ]);
        $patientUser->assignRole('patient');

        // 3. Therapist Profile
        $therapistProfile = TherapistProfile::create([
            'user_id' => $therapistUser->id,
            'license_no' => 'LIC-123456',
            'specialization' => 'Orthopedics',
        ]);
        $this->assertEquals($therapistUser->id, $therapistProfile->user->id);

        // 4. Patient Profile
        $patientProfile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'center_id' => $center->id,
            'therapist_id' => $therapistUser->id,
            'birth_date' => '1995-05-15',
            'current_week' => 2,
            'special_tests_file' => 'files/test.pdf',
        ]);
        $this->assertEquals($patientUser->id, $patientProfile->user->id);

        // 5. Treatment Plan
        $treatmentPlan = TreatmentPlan::create([
            'patient_id' => $patientUser->id,
            'therapist_id' => $therapistUser->id,
            'manual_therapy' => 'Spine mobilization',
            'goals' => 'Reduce pain by 50%',
        ]);
        $this->assertEquals($patientUser->id, $treatmentPlan->patient->id);

        // 6. Nutrition Plan
        $nutritionPlan = NutritionPlan::create([
            'patient_id' => $patientUser->id,
            'therapist_id' => $therapistUser->id,
            'breakfast' => 'Oats and milk',
        ]);
        $this->assertEquals($patientUser->id, $nutritionPlan->patient->id);

        // 7. Exercise & Patient Exercise & Log
        $exercise = Exercise::create([
            'center_id' => $center->id,
            'title' => 'Lower Back Stretch',
            'default_sets' => 3,
            'default_repeats' => 10,
            'default_duration' => 90,
            'video' => 'videos/stretch.mp4',
        ]);
        $patientExercise = PatientExercise::create([
            'patient_id' => $patientUser->id,
            'exercise_id' => $exercise->id,
            'assigned_by' => $therapistUser->id,
            'sets' => 3,
            'repeats' => 10,
            'duration' => 90,
        ]);
        $log = PatientExerciseLog::create([
            'patient_exercise_id' => $patientExercise->id,
            'patient_id' => $patientUser->id,
            'completed_sets' => 3,
            'completed_repeats' => 10,
            'duration_spent' => 90,
            'is_completed' => true,
            'logged_at' => now()->toDateString(),
        ]);
        $this->assertTrue($log->is_completed);

        // 8. Chat Message
        $chat = ChatMessage::create([
            'sender_id' => $therapistUser->id,
            'receiver_id' => $patientUser->id,
            'message' => 'Hello, please complete your exercise today.',
            'created_at' => now(),
        ]);
        $this->assertEquals($therapistUser->id, $chat->sender->id);

        // 9. Support Ticket
        $ticket = SupportTicket::create([
            'center_id' => $center->id,
            'user_id' => $patientUser->id,
            'subject' => 'App Issue',
            'message' => 'Cannot view timer',
        ]);
        $this->assertEquals('open', $ticket->status);

        // 10. Advertisement
        $ad = Advertisement::create([
            'title' => 'Summer Discount',
            'description' => '50% off subscription',
            'banner' => 'banners/summer.jpg',
            'start_at' => now()->toDateString(),
            'expire_at' => now()->addDays(30)->toDateString(),
        ]);
        $this->assertEquals('active', $ad->status);

        // 11. Testimonial
        $testimonial = Testimonial::create([
            'author_name' => 'Dr. Khaled',
            'author_title' => 'Clinic Owner',
            'avatar' => 'avatars/khaled.jpg',
            'content' => 'Great platform for center management.',
        ]);
        $this->assertTrue($testimonial->is_published);

        // 12. Clinical Director
        $director = ClinicalDirector::create([
            'name' => 'Prof. Hassan',
            'title' => 'Chief Clinical Director',
            'bio' => '20 years of experience in Physical Therapy.',
            'photo' => 'photos/hassan.jpg',
        ]);
        $this->assertDatabaseHas('clinical_directors', ['id' => $director->id]);
    }
}
