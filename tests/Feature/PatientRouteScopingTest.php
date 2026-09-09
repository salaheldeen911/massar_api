<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\PatientProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRouteScopingTest extends TestCase
{
    use RefreshDatabase;

    protected Center $center;
    protected User $admin;
    protected User $therapistA;
    protected User $therapistB;
    protected User $patientOfA;
    protected User $patientOfB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->center = Center::create([
            'name' => 'Massar Main Center',
            'phone' => '+201000000000',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        // Admin
        $this->admin = User::create([
            'center_id' => $this->center->id,
            'name' => 'Center Admin',
            'phone' => '+201011111111',
            'email' => 'admin@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        // Therapist A
        $this->therapistA = User::create([
            'center_id' => $this->center->id,
            'name' => 'Dr. Ahmed',
            'phone' => '+201022222222',
            'email' => 'ahmed@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapistA->assignRole('therapist');

        // Therapist B
        $this->therapistB = User::create([
            'center_id' => $this->center->id,
            'name' => 'Dr. Mona',
            'phone' => '+201033333333',
            'email' => 'mona@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapistB->assignRole('therapist');

        // Patient of Therapist A
        $this->patientOfA = User::create([
            'center_id' => $this->center->id,
            'name' => 'Patient Of Ahmed',
            'phone' => '+201044444444',
            'email' => 'patientA@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patientOfA->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->center->id,
            'user_id' => $this->patientOfA->id,
            'therapist_id' => $this->therapistA->id,
            'birth_date' => '1990-01-01',
        ]);

        // Patient of Therapist B
        $this->patientOfB = User::create([
            'center_id' => $this->center->id,
            'name' => 'Patient Of Mona',
            'phone' => '+201055555555',
            'email' => 'patientB@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patientOfB->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->center->id,
            'user_id' => $this->patientOfB->id,
            'therapist_id' => $this->therapistB->id,
            'birth_date' => '1992-02-02',
        ]);
    }

    public function test_admin_can_access_all_center_patients(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/business/patients');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $patientNames = collect($response->json('data'))->pluck('name')->toArray();

        $this->assertContains('Patient Of Ahmed', $patientNames);
        $this->assertContains('Patient Of Mona', $patientNames);
    }

    public function test_therapist_cannot_access_all_center_patients_endpoint(): void
    {
        $response = $this->actingAs($this->therapistA, 'sanctum')
            ->getJson('/api/business/patients');

        $response->assertStatus(403);
    }

    public function test_therapist_can_only_access_their_assigned_patients(): void
    {
        $response = $this->actingAs($this->therapistA, 'sanctum')
            ->getJson('/api/business/my-patients');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $patientNames = collect($response->json('data'))->pluck('name')->toArray();

        $this->assertContains('Patient Of Ahmed', $patientNames);
        $this->assertNotContains('Patient Of Mona', $patientNames);
    }
}
