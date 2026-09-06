<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_center_admin_can_register_pending_application(): void
    {
        $response = $this->postJson('/api/register', [
            'center_name' => 'Healing Hands Center',
            'center_phone' => '+201011112222',
            'specialty' => 'Orthopedics',
            'city' => 'Cairo',
            'name' => 'Dr. Ahmed Admin',
            'email' => 'ahmed@healing.com',
            'phone' => '+201011113333',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('center.status', 'pending')
            ->assertJsonPath('user.status', 'pending')
            ->assertJsonPath('user.roles.0', 'admin');

        $this->assertDatabaseHas('centers', [
            'name' => 'Healing Hands Center',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@healing.com',
            'status' => 'pending',
        ]);
    }

    public function test_pending_center_admin_cannot_login(): void
    {
        $center = Center::create([
            'name' => 'Pending Center',
            'phone' => '+201000000001',
            'city' => 'Cairo',
            'status' => 'pending',
        ]);

        $user = User::create([
            'center_id' => $center->id,
            'name' => 'Pending User',
            'phone' => '+201000000002',
            'email' => 'pending@center.com',
            'password' => bcrypt('password123'),
            'status' => 'pending',
        ]);
        $user->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'identity' => 'pending@center.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
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
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $token = $loginResponse->json('token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'active@center.com');
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
            ->assertJson(['message' => 'Successfully logged out.']);
    }
}
