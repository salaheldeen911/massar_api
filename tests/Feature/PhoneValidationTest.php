<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_user_can_register_center_with_local_egyptian_phone(): void
    {
        $payload = [
            'center_name' => 'Al-Amal Therapy Center',
            'specialty' => 'Physical Therapy',
            'country' => 'Egypt',
            'city' => 'Cairo',
            'therapists_count' => 3,
            'branches_count' => 1,
            'terms_accepted' => true,
            'name' => 'Dr. Ahmed',
            'email' => 'ahmed@test.com',
            'phone' => '01278945632', // Local Egyptian number without +
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        // Check that the phone was normalized to +201278945632 in the database
        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@test.com',
            'phone' => '+201278945632',
        ]);

        $this->assertDatabaseHas('centers', [
            'name' => 'Al-Amal Therapy Center',
            'phone' => '+201278945632',
        ]);
    }

    public function test_invalid_phone_returns_proper_english_error_message(): void
    {
        $payload = [
            'center_name' => 'Invalid Center',
            'therapists_count' => 1,
            'branches_count' => 1,
            'terms_accepted' => true,
            'name' => 'Dr. Invalid',
            'email' => 'invalid@test.com',
            'phone' => '12345', // Invalid short phone number
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $errors = $response->json('errors.phone');
        $this->assertContains('The phone number must be a valid phone number.', $errors);
    }

    public function test_international_format_with_plus_is_accepted(): void
    {
        $payload = [
            'center_name' => 'Gulf Therapy Center',
            'country' => 'Saudi Arabia',
            'city' => 'Riyadh',
            'therapists_count' => 2,
            'branches_count' => 1,
            'terms_accepted' => true,
            'name' => 'Dr. Fahad',
            'email' => 'fahad@test.com',
            'phone' => '+966501234567', // International format
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'fahad@test.com',
            'phone' => '+966501234567',
        ]);
    }

    public function test_local_saudi_number_is_auto_detected_and_normalized(): void
    {
        $payload = [
            'center_name' => 'Mobily Center',
            'country' => 'Saudi Arabia',
            'city' => 'Jeddah',
            'therapists_count' => 2,
            'branches_count' => 1,
            'terms_accepted' => true,
            'name' => 'Dr. Khaled',
            'email' => 'khaled@test.com',
            'phone' => '0541234567', // Local Saudi Mobily number without +
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'khaled@test.com',
            'phone' => '+966541234567',
        ]);
    }

    public function test_duplicate_phone_in_different_formats_is_blocked_by_uniqueness(): void
    {
        // First registration with international format
        $this->postJson('/api/register', [
            'center_name' => 'Center One',
            'therapists_count' => 1,
            'branches_count' => 1,
            'terms_accepted' => true,
            'name' => 'First User',
            'email' => 'first@test.com',
            'phone' => '+201278945632',
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ])->assertStatus(201);

        // Second registration attempting the same number in local format
        $response = $this->postJson('/api/register', [
            'center_name' => 'Center Two',
            'therapists_count' => 1,
            'branches_count' => 1,
            'terms_accepted' => true,
            'name' => 'Second User',
            'email' => 'second@test.com',
            'phone' => '01278945632', // local format of the same phone
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_user_can_login_using_local_phone_format(): void
    {
        // Create user with normalized international phone
        $center = Center::create([
            'name' => 'Active Clinic',
            'phone' => '+201278945632',
            'status' => 'active',
        ]);

        $user = User::create([
            'center_id' => $center->id,
            'name' => 'Doctor Login',
            'phone' => '+201278945632',
            'email' => 'login@test.com',
            'password' => Hash::make('Secret1234!'),
            'status' => 'active',
        ]);
        $user->assignRole('admin');

        // Login using local format without +20
        $response = $this->postJson('/api/login', [
            'identity' => '01278945632',
            'password' => 'Secret1234!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_admin_can_create_patient_with_local_phone_format(): void
    {
        $center = Center::create([
            'name' => 'Healing Center',
            'phone' => '+201278945632',
            'status' => 'active',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Admin Boss',
            'phone' => '+201011110000',
            'email' => 'boss@healing.com',
            'password' => Hash::make('Secret1234!'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/business/patients', [
            'name' => 'Local Patient',
            'phone' => '01099887766', // Local Egyptian number
            'password' => 'Secret1234!',
            'birth_date' => '2000-01-01',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone', '+201099887766');

        $this->assertDatabaseHas('users', [
            'name' => 'Local Patient',
            'phone' => '+201099887766',
        ]);
    }

    public function test_admin_can_create_therapist_with_local_phone_format(): void
    {
        $center = Center::create([
            'name' => 'Physio Pro',
            'phone' => '+201278945632',
            'status' => 'active',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Admin Boss 2',
            'phone' => '+201011110001',
            'email' => 'boss2@healing.com',
            'password' => Hash::make('Secret1234!'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->postJson('/api/business/therapists', [
            'name' => 'Local Therapist',
            'email' => 'therapist@physio.com',
            'phone' => '01122334455', // Local Egyptian number
            'password' => 'Secret1234!',
            'specialization' => 'Orthopedic Physical Therapy',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone', '+201122334455');

        $this->assertDatabaseHas('users', [
            'email' => 'therapist@physio.com',
            'phone' => '+201122334455',
        ]);
    }
}

