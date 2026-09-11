<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_center_admin_can_register_pending_application_with_license_document(): void
    {
        Storage::fake('public');

        $licenseFile = UploadedFile::fake()->create('license_doc.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/register', [
            'center_name' => 'Healing Hands Center',
            'specialty' => 'Orthopedics',
            'city' => 'Cairo',
            'therapists_count' => 5,
            'branches_count' => 2,
            'referral_source' => 'Google Search',
            'terms_accepted' => true,
            'license_document' => $licenseFile,
            'name' => 'Dr. Ahmed Admin',
            'email' => 'ahmed@healing.com',
            'phone' => '+201011113333',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.center.status', 'pending')
            ->assertJsonPath('data.center.phone', '+201011113333')
            ->assertJsonPath('data.center.therapists_count', 5)
            ->assertJsonPath('data.center.branches_count', 2)
            ->assertJsonPath('data.center.referral_source', 'Google Search')
            ->assertJsonPath('data.center.terms_accepted', true)
            ->assertJsonPath('data.user.status', 'pending')
            ->assertJsonPath('data.user.phone', '+201011113333')
            ->assertJsonPath('data.user.roles.0', 'admin')
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertNotNull($response->json('data.token'));

        $this->assertDatabaseHas('centers', [
            'name' => 'Healing Hands Center',
            'phone' => '+201011113333',
            'therapists_count' => 5,
            'branches_count' => 2,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@healing.com',
            'phone' => '+201011113333',
            'status' => 'pending',
        ]);

        $center = Center::where('name', 'Healing Hands Center')->first();
        $this->assertTrue($center->hasMedia('license_document'));
    }

    public function test_center_admin_can_register_using_figma_payload_without_center_phone_or_city(): void
    {
        $response = $this->postJson('/api/register', [
            'center_name' => 'Kinetic Physical Therapy Center',
            'name' => 'Full Admin Name',
            'specialty' => 'Orthopedic',
            'phone' => '+201112223344',
            'email' => 'kinetic@center.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'branches_count' => 1,
            'therapists_count' => 5,
            'referral_source' => 'Social Media',
            'terms_accepted' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.center.name', 'Kinetic Physical Therapy Center')
            ->assertJsonPath('data.center.phone', '+201112223344')
            ->assertJsonPath('data.center.country', null)
            ->assertJsonPath('data.center.city', null)
            ->assertJsonPath('data.user.name', 'Full Admin Name');

        $this->assertNotNull($response->json('data.token'));
    }

    public function test_inactive_center_admin_cannot_login(): void
    {
        $center = Center::create([
            'name' => 'Inactive Center',
            'phone' => '+201000000001',
            'city' => 'Cairo',
            'status' => 'suspended',
        ]);

        $user = User::create([
            'center_id' => $center->id,
            'name' => 'Inactive User',
            'phone' => '+201000000002',
            'email' => 'inactive@center.com',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
        ]);
        $user->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'identity' => 'inactive@center.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['identity']);
    }

    public function test_active_user_can_login_and_access_me(): void
    {
        $center = Center::create([
            'name' => 'Active Center',
            'phone' => '+201000000003',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $user = User::create([
            'center_id' => $center->id,
            'name' => 'Active User',
            'phone' => '+201000000004',
            'email' => 'active@center.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $user->assignRole('admin');

        $loginResponse = $this->postJson('/api/login', [
            'identity' => 'active@center.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $token = $loginResponse->json('data.token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'active@center.com');
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'User Logout',
            'phone' => '+201000000005',
            'email' => 'logout@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJson(['message' => 'Successfully logged out.']);
    }

    public function test_patient_login_returns_therapist_name(): void
    {
        $center = Center::create([
            'name' => 'Active Center',
            'phone' => '+201000000010',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $therapist = User::create([
            'center_id' => $center->id,
            'name' => 'Dr. Sara Mohamed',
            'phone' => '+201000000011',
            'email' => 'sara@center.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');

        $patient = User::create([
            'center_id' => $center->id,
            'name' => 'Ali Patient',
            'phone' => '+201000000012',
            'email' => 'ali@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patient->assignRole('patient');

        \App\Models\PatientProfile::create([
            'user_id' => $patient->id,
            'center_id' => $center->id,
            'therapist_id' => $therapist->id,
            'birth_date' => '1998-01-01',
        ]);

        $response = $this->postJson('/api/login', [
            'identity' => 'ali@patient.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.therapist_name', 'Dr. Sara Mohamed');
    }
}
