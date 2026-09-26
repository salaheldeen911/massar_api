<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\NutritionPlan;
use App\Models\TreatmentPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_auth_routes_rate_limiting_blocks_excessive_login_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'identity' => 'nonexistent@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should return 429 Too Many Requests
        $response = $this->postJson('/api/login', [
            'identity' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    public function test_clinical_plans_are_strictly_isolated_between_centers(): void
    {
        $centerA = Center::create(['name' => 'Center Alpha', 'phone' => '+201000000001', 'city' => 'Cairo']);
        $centerB = Center::create(['name' => 'Center Beta', 'phone' => '+201000000002', 'city' => 'Alexandria']);

        $patientA = User::create([
            'center_id' => $centerA->id,
            'name' => 'Patient Alpha',
            'phone' => '+201011111111',
            'password' => bcrypt('password123'),
        ]);
        $patientA->assignRole('patient');

        $patientB = User::create([
            'center_id' => $centerB->id,
            'name' => 'Patient Beta',
            'phone' => '+201022222222',
            'password' => bcrypt('password123'),
        ]);
        $patientB->assignRole('patient');

        $planA = TreatmentPlan::create([
            'patient_id' => $patientA->id,
            'center_id' => $centerA->id,
            'manual_therapy' => 'Alpha Therapy',
        ]);

        $planB = TreatmentPlan::create([
            'patient_id' => $patientB->id,
            'center_id' => $centerB->id,
            'manual_therapy' => 'Beta Therapy',
        ]);

        // Patient A can view their plan
        $responseA = $this->actingAs($patientA, 'sanctum')->getJson('/api/patient/treatment-plan');
        $responseA->assertStatus(200)
            ->assertJsonPath('data.treatment_plan.manual_therapy', 'Alpha Therapy');

        // Patient B can view their plan
        $responseB = $this->actingAs($patientB, 'sanctum')->getJson('/api/patient/treatment-plan');
        $responseB->assertStatus(200)
            ->assertJsonPath('data.treatment_plan.manual_therapy', 'Beta Therapy');

        // Center A scope cannot query Center B's treatment plan
        $this->actingAs($patientA, 'sanctum');
        $this->assertNull(TreatmentPlan::where('id', $planB->id)->first());
    }

    public function test_nutrition_plans_are_strictly_isolated_between_centers(): void
    {
        $centerA = Center::create(['name' => 'Center Alpha', 'phone' => '+201000000001', 'city' => 'Cairo']);
        $centerB = Center::create(['name' => 'Center Beta', 'phone' => '+201000000002', 'city' => 'Alexandria']);

        $patientA = User::create([
            'center_id' => $centerA->id,
            'name' => 'Patient Alpha',
            'phone' => '+201011111111',
            'password' => bcrypt('password123'),
        ]);
        $patientA->assignRole('patient');

        $patientB = User::create([
            'center_id' => $centerB->id,
            'name' => 'Patient Beta',
            'phone' => '+201022222222',
            'password' => bcrypt('password123'),
        ]);
        $patientB->assignRole('patient');

        $nutrA = NutritionPlan::create([
            'patient_id' => $patientA->id,
            'center_id' => $centerA->id,
            'breakfast' => 'Oatmeal Alpha',
        ]);

        $nutrB = NutritionPlan::create([
            'patient_id' => $patientB->id,
            'center_id' => $centerB->id,
            'breakfast' => 'Oatmeal Beta',
        ]);

        // Patient A views their plan
        $responseA = $this->actingAs($patientA, 'sanctum')->getJson('/api/patient/nutrition-plan');
        $responseA->assertStatus(200)
            ->assertJsonPath('data.nutrition_program.breakfast', 'Oatmeal Alpha');

        // Center A scope cannot query Center B's nutrition plan
        $this->actingAs($patientA, 'sanctum');
        $this->assertNull(NutritionPlan::where('id', $nutrB->id)->first());
    }
}