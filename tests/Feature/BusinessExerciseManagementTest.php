<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Exercise;
use App\Models\PatientProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessExerciseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Center $center;
    protected User $admin;
    protected User $therapist;
    protected User $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->center = Center::create([
            'name' => 'Massar Medical Center',
            'phone' => '+201000000000',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'center_id' => $this->center->id,
            'name' => 'Center Admin',
            'phone' => '+201011111111',
            'email' => 'admin@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        $this->therapist = User::create([
            'center_id' => $this->center->id,
            'name' => 'Dr. Therapist',
            'phone' => '+201022222222',
            'email' => 'therapist@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapist->assignRole('therapist');

        $this->patient = User::create([
            'center_id' => $this->center->id,
            'name' => 'Patient Care',
            'phone' => '+201033333333',
            'email' => 'patient@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patient->assignRole('patient');

        PatientProfile::create([
            'center_id' => $this->center->id,
            'user_id' => $this->patient->id,
            'therapist_id' => $this->therapist->id,
            'birth_date' => '1995-01-01',
        ]);
    }

    public function test_admin_can_create_center_exercise_and_assign_to_patient(): void
    {
        Storage::fake('public');

        $video = UploadedFile::fake()->create('exercise.mp4', 1024, 'video/mp4');

        // Admin creates exercise
        $createResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/business/exercises', [
                'title' => 'Admin Public Exercise',
                'default_sets' => 4,
                'default_repeats' => 15,
                'default_duration' => 120,
                'therapist_notes' => 'Admin instructions',
                'video' => $video,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Admin Public Exercise');

        $exerciseId = $createResponse->json('data.id');

        // Admin assigns exercise to patient
        $assignResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/business/patients/{$this->patient->id}/exercises", [
                'exercise_id' => $exerciseId,
                'sets' => 4,
                'repeats' => 15,
                'duration' => 120,
                'notes' => 'Perform twice daily',
            ]);

        $assignResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Admin Public Exercise')
            ->assertJsonPath('data.sets', 4);
    }

    public function test_therapist_can_create_exercise_and_assign_to_patient(): void
    {
        Storage::fake('public');

        $video = UploadedFile::fake()->create('therapist_ex.mp4', 1024, 'video/mp4');

        // Therapist creates exercise
        $createResponse = $this->actingAs($this->therapist, 'sanctum')
            ->postJson('/api/business/exercises', [
                'title' => 'Therapist Private Exercise',
                'default_sets' => 3,
                'default_repeats' => 10,
                'default_duration' => 60,
                'therapist_notes' => 'Therapist instructions',
                'video' => $video,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Therapist Private Exercise');

        $exerciseId = $createResponse->json('data.id');

        // Therapist assigns exercise to patient
        $assignResponse = $this->actingAs($this->therapist, 'sanctum')
            ->postJson("/api/business/patients/{$this->patient->id}/exercises", [
                'exercise_id' => $exerciseId,
                'sets' => 3,
                'repeats' => 10,
                'duration' => 60,
            ]);

        $assignResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Therapist Private Exercise');
    }
}
