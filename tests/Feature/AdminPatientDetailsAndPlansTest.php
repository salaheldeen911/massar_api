<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Exercise;
use App\Models\PatientExercise;
use App\Models\PatientProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPatientDetailsAndPlansTest extends TestCase
{
    use RefreshDatabase;

    protected Center $centerA;
    protected User $adminUserA;
    protected User $therapistA;
    protected User $patientUserA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->centerA = Center::create([
            'name' => 'Alpha Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->adminUserA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Admin Alpha',
            'phone' => '+201011110000',
            'email' => 'admin@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminUserA->assignRole('admin');

        $this->therapistA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Ahmed Ali',
            'phone' => '+201011113333',
            'email' => 'ahmed@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapistA->assignRole('therapist');

        $this->patientUserA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Patient Mohamed',
            'phone' => '+201011114444',
            'email' => 'mohamed@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patientUserA->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->centerA->id,
            'user_id' => $this->patientUserA->id,
            'therapist_id' => $this->therapistA->id,
            'birth_date' => '1995-05-15',
            'current_week' => 4,
            'patient_history' => 'No surgeries',
            'chief_complain' => 'Back pain',
            'diagnosis' => 'Muscle spasm',
        ]);
    }

    public function test_admin_can_get_rich_4_tab_patient_details(): void
    {
        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson("/api/business/patients/{$this->patientUserA->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'header_info' => ['id', 'name', 'birth_date', 'age', 'current_week', 'assigned_therapist'],
                    'patient_details' => ['patient_history', 'chief_complain', 'diagnosis', 'special_tests_notes', 'objective_findings'],
                    'treatment_plan',
                    'nutrition_program',
                    'exercises_list',
                ],
            ]);

        $this->assertEquals('Patient Mohamed', $response->json('data.header_info.name'));
        $this->assertEquals('Back pain', $response->json('data.patient_details.chief_complain'));
    }

    public function test_admin_can_save_treatment_plan_for_patient(): void
    {
        $payload = [
            'manual_therapy' => 'Spine mobilization summary.',
            'electrotherapy' => 'TENS 20 mins.',
            'medications' => 'Painkillers as prescribed.',
            'goals' => 'Full range of motion in 4 weeks.',
        ];

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson("/api/business/patients/{$this->patientUserA->id}/treatment-plan", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.manual_therapy', 'Spine mobilization summary.');

        $this->assertDatabaseHas('treatment_plans', [
            'patient_id' => $this->patientUserA->id,
            'manual_therapy' => 'Spine mobilization summary.',
        ]);
    }

    public function test_admin_can_save_nutrition_plan_for_patient(): void
    {
        $payload = [
            'breakfast' => 'Oatmeal & eggs',
            'lunch' => 'Grilled chicken & salad',
            'dinner' => 'Greek yogurt & berries',
            'snacks' => 'Almonds & green tea',
            'supplements' => 'Omega-3 & Vitamin D3',
            'foods_to_avoid' => 'Refined sugars & soda',
        ];

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson("/api/business/patients/{$this->patientUserA->id}/nutrition-plan", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.breakfast', 'Oatmeal & eggs');

        $this->assertDatabaseHas('nutrition_plans', [
            'patient_id' => $this->patientUserA->id,
            'breakfast' => 'Oatmeal & eggs',
        ]);
    }

    public function test_three_tier_exercise_library_scoping(): void
    {
        // 1. Global exercise (center_id = null)
        Exercise::withoutGlobalScope('center_scope')->create([
            'center_id' => null,
            'therapist_id' => null,
            'title' => 'Global Jumping Jacks',
        ]);

        // 2. Center public exercise (center_id = centerA, therapist_id = null)
        Exercise::withoutGlobalScope('center_scope')->create([
            'center_id' => $this->centerA->id,
            'therapist_id' => null,
            'title' => 'Alpha Center Public Pushups',
        ]);

        // 3. Therapist private exercise (center_id = centerA, therapist_id = therapistA)
        Exercise::withoutGlobalScope('center_scope')->create([
            'center_id' => $this->centerA->id,
            'therapist_id' => $this->therapistA->id,
            'title' => 'Dr Ahmed Private Squats',
        ]);

        // Admin of center A should see exercises
        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson('/api/business/exercises');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();

        $this->assertContains('Global Jumping Jacks', $titles);
        $this->assertContains('Alpha Center Public Pushups', $titles);
    }

    public function test_admin_can_assign_and_unassign_exercise_to_patient(): void
    {
        Storage::fake('media');

        $exercise = Exercise::create([
            'center_id' => $this->centerA->id,
            'title' => 'Hamstring Stretch',
            'default_sets' => 3,
            'default_repeats' => 10,
            'default_duration' => 60,
        ]);

        // Assign
        $assignResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson("/api/business/patients/{$this->patientUserA->id}/exercises", [
                'exercise_id' => $exercise->id,
                'sets' => 4,
                'repeats' => 12,
            ]);

        $assignResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Hamstring Stretch')
            ->assertJsonPath('data.sets', 4);

        $patientExerciseId = $assignResponse->json('data.id');

        // Unassign
        $unassignResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->deleteJson("/api/business/patients/{$this->patientUserA->id}/exercises/{$patientExerciseId}");

        $unassignResponse->assertStatus(200);

        $this->assertDatabaseMissing('patient_exercises', ['id' => $patientExerciseId]);
    }
}
