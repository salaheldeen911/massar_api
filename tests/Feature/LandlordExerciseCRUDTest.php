<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;

use App\Models\Center;
use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordExerciseCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlordUser;
    protected Center $center1;
    protected Center $center2;
    protected User $therapist1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->landlordUser = User::create([
            'name' => 'Global Landlord Admin',
            'phone' => '+201099999999',
            'email' => 'landlord@massar.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'center_id' => null,
        ]);
        $this->landlordUser->assignRole('landlord');

        $this->center1 = Center::create([
            'name' => 'Cairo Physical Therapy Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->center2 = Center::create([
            'name' => 'Alexandria Rehab Center',
            'phone' => '+201011113333',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $this->therapist1 = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Dr. Ahmed Therapist',
            'phone' => '+201011114444',
            'email' => 'ahmed@center1.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $this->therapist1->assignRole('therapist');
    }

    public function test_landlord_lists_global_system_exercises_by_default(): void
    {
        $globalExercise = Exercise::create([
            'center_id' => null,
            'therapist_id' => null,
            'title' => 'Global System Squat',
        ]);

        $centerExercise = Exercise::create([
            'center_id' => $this->center1->id,
            'therapist_id' => null,
            'title' => 'Center 1 Custom Exercise',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/exercises');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Global System Squat')
            ->assertJsonPath('data.0.is_global', true);
    }

    public function test_landlord_can_filter_exercises_by_center_id(): void
    {
        $globalExercise = Exercise::create([
            'center_id' => null,
            'therapist_id' => null,
            'title' => 'Global System Stretch',
        ]);

        $centerPublicExercise = Exercise::create([
            'center_id' => $this->center1->id,
            'therapist_id' => null,
            'title' => 'Center 1 Public Exercise',
        ]);

        $therapistPrivateExercise = Exercise::create([
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'title' => 'Therapist 1 Private Exercise',
        ]);

        $center2Exercise = Exercise::create([
            'center_id' => $this->center2->id,
            'therapist_id' => null,
            'title' => 'Center 2 Exercise',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/exercises?center_id={$this->center1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2);

        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertContains('Center 1 Public Exercise', $titles);
        $this->assertContains('Therapist 1 Private Exercise', $titles);
        $this->assertNotContains('Global System Stretch', $titles);
        $this->assertNotContains('Center 2 Exercise', $titles);
    }

    public function test_landlord_can_filter_exercises_by_therapist_id(): void
    {
        Exercise::create([
            'center_id' => $this->center1->id,
            'therapist_id' => null,
            'title' => 'Center 1 Public Exercise',
        ]);

        Exercise::create([
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'title' => 'Dr Ahmed Private Exercise',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/exercises?center_id={$this->center1->id}&therapist_id={$this->therapist1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Dr Ahmed Private Exercise');
    }

    public function test_landlord_can_create_update_and_delete_any_exercise(): void
    {
        Storage::fake('media');
        $videoFile = UploadedFile::fake()->create('exercise_demo.mp4', 1024, 'video/mp4');

        // 1. Create Global Exercise
        $createResponse = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson('/api/landlord/exercises', [
                'title' => 'Landlord Created Global Exercise',
                'default_sets' => 3,
                'default_repeats' => 12,
                'default_duration' => 90,
                'therapist_notes' => 'Landlord global instructions',
                'video' => $videoFile,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Landlord Created Global Exercise')
            ->assertJsonPath('data.is_global', true);

        $exerciseId = $createResponse->json('data.id');

        // 2. Show Exercise
        $showResponse = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/exercises/{$exerciseId}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('data.title', 'Landlord Created Global Exercise');

        // 3. Update Exercise to assign to Center 1
        $updateResponse = $this->actingAs($this->landlordUser, 'sanctum')
            ->putJson("/api/landlord/exercises/{$exerciseId}", [
                'title' => 'Updated Global Exercise Title',
                'center_id' => $this->center1->id,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Global Exercise Title')
            ->assertJsonPath('data.center_id', $this->center1->id);

        // 4. Delete Exercise
        $deleteResponse = $this->actingAs($this->landlordUser, 'sanctum')
            ->deleteJson("/api/landlord/exercises/{$exerciseId}");

        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('exercises', ['id' => $exerciseId]);
    }
}
