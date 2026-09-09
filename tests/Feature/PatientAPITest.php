<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Diagnosis;
use App\Models\Exercise;
use App\Models\NutritionPlan;
use App\Models\PatientExercise;
use App\Models\PatientProfile;
use App\Models\TherapistProfile;
use App\Models\TreatmentPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientAPITest extends TestCase
{
    use RefreshDatabase;

    protected Center $center;
    protected User $therapistUser;
    protected User $patientUser;
    protected PatientProfile $patientProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->center = Center::create([
            'name' => 'Massar Rehab Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->therapistUser = User::create([
            'center_id' => $this->center->id,
            'name' => 'Dr. Mohamed Ahmed',
            'phone' => '+201012345678',
            'email' => 'therapist@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapistUser->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $this->therapistUser->id,
            'specialization' => 'Physical Therapy',
        ]);

        $diagnosis = Diagnosis::create([
            'name' => 'Anterior Cruciate Ligament Injury',
            'code' => 'ACL',
            'category_letter' => 'A',
        ]);

        $this->patientUser = User::create([
            'center_id' => $this->center->id,
            'name' => 'Seif Mohamed',
            'phone' => '+201087654321',
            'email' => 'seif@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patientUser->assignRole('patient');

        $this->patientProfile = PatientProfile::create([
            'center_id' => $this->center->id,
            'user_id' => $this->patientUser->id,
            'therapist_id' => $this->therapistUser->id,
            'diagnosis_id' => $diagnosis->id,
            'birth_date' => '2000-07-23',
            'current_week' => 4,
            'chief_complain' => 'Knee instability and swelling',
            'patient_history' => 'Sports injury during football match',
        ]);
    }

    public function test_patient_can_login_with_phone_number_and_password(): void
    {
        $response = $this->postJson('/api/login', [
            'identity' => '+201087654321',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Seif Mohamed')
            ->assertJsonPath('data.user.phone', '+201087654321');

        $this->assertNotNull($response->json('data.token'));
    }

    public function test_patient_can_view_treatment_plan(): void
    {
        TreatmentPlan::create([
            'patient_id' => $this->patientUser->id,
            'manual_therapy' => 'Joint mobilization 30 mins',
            'electrotherapy' => 'TENS therapy 20 mins',
            'medications' => 'Anti-inflammatory as prescribed',
            'goals' => 'Knee flex 120 degrees',
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->getJson('/api/patient/treatment-plan');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.header_info.name', 'Seif Mohamed')
            ->assertJsonPath('data.header_info.current_week', 4)
            ->assertJsonPath('data.header_info.therapist_name', 'Dr. Mohamed Ahmed')
            ->assertJsonPath('data.header_info.diagnosis_info.code', 'ACL')
            ->assertJsonPath('data.treatment_plan.manual_therapy', 'Joint mobilization 30 mins')
            ->assertJsonPath('data.treatment_plan.goals', 'Knee flex 120 degrees');
    }

    public function test_patient_can_view_nutrition_plan(): void
    {
        NutritionPlan::create([
            'patient_id' => $this->patientUser->id,
            'breakfast' => 'Oats with milk and berries',
            'lunch' => 'Grilled chicken breast with rice',
            'dinner' => 'Greek yogurt with nuts',
            'snacks' => 'Almonds and apples',
            'supplements' => 'Omega-3 and Vitamin D3',
            'foods_to_avoid' => 'Soda and fried food',
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->getJson('/api/patient/nutrition-plan');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nutrition_program.breakfast', 'Oats with milk and berries')
            ->assertJsonPath('data.nutrition_program.lunch', 'Grilled chicken breast with rice')
            ->assertJsonPath('data.nutrition_program.supplements', 'Omega-3 and Vitamin D3');
    }

    public function test_patient_can_view_assigned_exercises_and_progress_overview(): void
    {
        $exercise = Exercise::create([
            'center_id' => $this->center->id,
            'title' => 'Quadriceps Setting',
            'default_sets' => 3,
            'default_repeats' => 12,
            'default_duration' => 90,
        ]);

        PatientExercise::create([
            'patient_id' => $this->patientUser->id,
            'exercise_id' => $exercise->id,
            'assigned_by' => $this->therapistUser->id,
            'sets' => 3,
            'repeats' => 12,
            'duration' => 90,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->getJson('/api/patient/exercises');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.header_summary.total_assigned_count', 1)
            ->assertJsonPath('data.header_summary.completion_percentage', 0)
            ->assertJsonPath('data.exercises_list.0.title', 'Quadriceps Setting')
            ->assertJsonPath('data.exercises_list.0.status', 'pending');
    }

    public function test_patient_can_log_exercise_progress_and_mark_completed(): void
    {
        $exercise = Exercise::create([
            'center_id' => $this->center->id,
            'title' => 'Straight Leg Raise',
            'default_sets' => 3,
            'default_repeats' => 10,
        ]);

        $patientExercise = PatientExercise::create([
            'patient_id' => $this->patientUser->id,
            'exercise_id' => $exercise->id,
            'assigned_by' => $this->therapistUser->id,
            'sets' => 3,
            'repeats' => 10,
            'duration' => 60,
            'status' => 'pending',
        ]);

        // 1. Log partial progress
        $logResponse = $this->actingAs($this->patientUser, 'sanctum')
            ->postJson("/api/patient/exercises/{$patientExercise->id}/log", [
                'completed_sets' => 1,
                'completed_repeats' => 10,
            ]);

        $logResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.today_progress.completed_sets', 1);

        // 2. Mark complete
        $completeResponse = $this->actingAs($this->patientUser, 'sanctum')
            ->postJson("/api/patient/exercises/{$patientExercise->id}/complete");

        $completeResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.today_progress.is_completed', true);

        // 3. Therapist views patient profile and sees completed status
        $therapistViewResponse = $this->actingAs($this->therapistUser, 'sanctum')
            ->getJson("/api/business/patients/{$this->patientUser->id}");

        $therapistViewResponse->assertStatus(200)
            ->assertJsonPath('data.exercises_list.0.status', 'completed')
            ->assertJsonPath('data.exercises_list.0.today_progress.is_completed', true);
    }
}
