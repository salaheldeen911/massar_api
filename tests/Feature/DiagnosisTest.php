<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Diagnosis;
use App\Models\User;
use Database\Seeders\DiagnosisSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosisTest extends TestCase
{
    use RefreshDatabase;

    protected Center $center;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, DiagnosisSeeder::class]);

        $this->center = Center::create([
            'name' => 'Massar Medical Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->adminUser = User::create([
            'center_id' => $this->center->id,
            'name' => 'Center Admin',
            'phone' => '+201011110000',
            'email' => 'admin@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminUser->assignRole('admin');
    }

    public function test_seeder_populates_diagnoses_table(): void
    {
        $this->assertDatabaseHas('diagnoses', [
            'name' => 'Anterior Cruciate Ligament Injury',
            'code' => 'ACL',
            'category_letter' => 'A',
        ]);

        $this->assertDatabaseHas('diagnoses', [
            'name' => 'Carpal Tunnel Syndrome',
            'code' => 'CTS',
            'category_letter' => 'C',
        ]);

        $this->assertGreaterThanOrEqual(100, Diagnosis::count());
    }

    public function test_authenticated_user_can_list_diagnoses_catalog(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/diagnoses');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'code', 'category_letter'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_can_filter_diagnoses_by_search_query(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/diagnoses?search=Cruciate');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.code', 'ACL');
    }

    public function test_can_filter_diagnoses_by_category_letter(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/diagnoses?category_letter=K');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $codes = collect($response->json('data'))->pluck('code')->all();
        $this->assertContains('KOA', $codes);
    }

    public function test_admin_can_assign_diagnosis_id_to_patient(): void
    {
        $diagnosis = Diagnosis::where('code', 'ACL')->firstOrFail();

        $payload = [
            'name' => 'Patient Knee Issue',
            'phone' => '+201099887766',
            'password' => 'password123',
            'birth_date' => '1995-05-15',
            'diagnosis_id' => $diagnosis->id,
            'diagnosis' => 'Custom text notes on ACL tear',
        ];

        $createResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/business/patients', $payload);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.diagnosis_id', $diagnosis->id)
            ->assertJsonPath('data.diagnosis_info.code', 'ACL')
            ->assertJsonPath('data.diagnosis_info.name', 'Anterior Cruciate Ligament Injury');

        $patientId = $createResponse->json('data.id');

        $showResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/business/patients/{$patientId}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('data.patient_details.diagnosis_id', $diagnosis->id)
            ->assertJsonPath('data.patient_details.diagnosis_info.code', 'ACL');
    }
}
