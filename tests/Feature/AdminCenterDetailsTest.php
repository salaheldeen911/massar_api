<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCenterDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected Center $center;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->center = Center::create([
            'name' => 'Massar Original Clinic',
            'phone' => '+201114543532',
            'email' => 'info@massar.com',
            'city' => 'Cairo',
            'status' => 'active',
            'facebook' => 'https://facebook.com/massar',
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

    public function test_admin_can_view_center_details(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/business/center-details');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Massar Original Clinic')
            ->assertJsonPath('data.email', 'info@massar.com')
            ->assertJsonPath('data.phone', '+201114543532')
            ->assertJsonPath('data.facebook', 'https://facebook.com/massar');
    }

    public function test_admin_can_update_center_website_info_and_social_links(): void
    {
        $payload = [
            'name' => 'Massar Advanced Center',
            'email' => 'support@massar.com',
            'phone' => '+201114549999',
            'facebook' => 'https://facebook.com/massar-official',
            'whatsapp' => '+201114549999',
            'instagram' => 'https://instagram.com/massar',
            'linkedin' => 'https://linkedin.com/company/massar',
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/business/center-details', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Massar Advanced Center')
            ->assertJsonPath('data.email', 'support@massar.com')
            ->assertJsonPath('data.whatsapp', '+201114549999')
            ->assertJsonPath('data.linkedin', 'https://linkedin.com/company/massar');

        $this->assertDatabaseHas('centers', [
            'id' => $this->center->id,
            'name' => 'Massar Advanced Center',
            'email' => 'support@massar.com',
            'whatsapp' => '+201114549999',
        ]);
    }

    public function test_admin_can_upload_center_logo(): void
    {
        Storage::fake('media');

        $logoFile = UploadedFile::fake()->image('center_logo.png', 400, 400);

        $payload = [
            'name' => 'Massar Original Clinic',
            'phone' => '+201114543532',
            'logo' => $logoFile,
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/business/center-details', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotNull($response->json('data.logo_url'));
    }

    public function test_therapist_cannot_access_center_details(): void
    {
        $therapist = User::create([
            'center_id' => $this->center->id,
            'name' => 'Therapist User',
            'phone' => '+201099887766',
            'email' => 'therapist@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');

        $response = $this->actingAs($therapist, 'sanctum')
            ->getJson('/api/business/center-details');

        $response->assertStatus(403);
    }
}
