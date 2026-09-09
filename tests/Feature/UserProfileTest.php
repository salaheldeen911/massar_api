<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Center $center;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->center = Center::create([
            'name' => 'Test Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'center_id' => $this->center->id,
            'name' => 'Original Name',
            'phone' => '+201099887766',
            'email' => 'original@massar.com',
            'password' => bcrypt('oldpassword123'),
            'status' => 'active',
        ]);
        $this->user->assignRole('admin');
    }

    public function test_authenticated_user_can_update_profile_info_and_avatar(): void
    {
        Storage::fake('media');

        $avatarFile = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@massar.com',
            'phone' => '+201099887700',
            'avatar' => $avatarFile,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profile', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@massar.com')
            ->assertJsonPath('data.phone', '+201099887700');

        $this->assertNotNull($response->json('data.avatar_url'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@massar.com',
        ]);
    }

    public function test_user_can_change_password_with_valid_current_password_and_confirmation(): void
    {
        $payload = [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profile/password', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password changed successfully.');

        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    }

    public function test_change_password_fails_if_current_password_is_incorrect(): void
    {
        $payload = [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profile/password', $payload);

        $response->assertStatus(422);
    }

    public function test_change_password_fails_without_confirmation(): void
    {
        $payload = [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'mismatchpassword',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profile/password', $payload);

        $response->assertStatus(422);
    }
}
