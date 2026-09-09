<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlordUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->landlordUser = User::create([
            'name' => 'Landlord Admin',
            'phone' => '+201099999999',
            'email' => 'landlord@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'center_id' => null,
        ]);
        $this->landlordUser->assignRole('landlord');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/landlord/dashboard');

        $response->assertStatus(401);
    }

    public function test_non_landlord_user_cannot_access_dashboard(): void
    {
        $center = Center::create([
            'name' => 'Test Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $adminUser = User::create([
            'center_id' => $center->id,
            'name' => 'Center Admin',
            'phone' => '+201022223333',
            'email' => 'admin@testcenter.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $adminUser->assignRole('admin');

        $response = $this->actingAs($adminUser, 'sanctum')
            ->getJson('/api/landlord/dashboard');

        $response->assertStatus(403);
    }

    public function test_landlord_can_get_dashboard_data_structure(): void
    {
        $center1 = Center::create([
            'name' => 'GreenTech Labs',
            'phone' => '+201011112222',
            'specialty' => 'Physical Therapy',
            'city' => 'Cairo',
            'country' => 'Egypt',
            'status' => 'active',
        ]);

        $center2 = Center::create([
            'name' => 'Healing Hands',
            'phone' => '+201033334444',
            'specialty' => 'Orthopedic',
            'city' => 'Giza',
            'country' => 'Egypt',
            'status' => 'pending',
        ]);

        SupportTicket::create([
            'center_id' => $center1->id,
            'user_id' => $this->landlordUser->id,
            'subject' => 'Integration Assistance Needed',
            'message' => 'Need help setting up Reverb.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'stats' => [
                        'pending_requests' => ['count', 'change_percentage', 'period'],
                        'total_centers' => ['count', 'change_percentage', 'period'],
                        'total_users' => ['count', 'change_percentage', 'period'],
                    ],
                    'quick_actions' => [
                        'add_new_center' => ['title', 'description'],
                        'review_requests' => ['title', 'count', 'label'],
                        'center_support' => ['title', 'count', 'label'],
                    ],
                    'recent_activities',
                    'recent_centers',
                ],
            ]);

        $this->assertEquals(1, $response->json('data.stats.pending_requests.count'));
        $this->assertEquals(2, $response->json('data.stats.total_centers.count'));
        $this->assertEquals(1, $response->json('data.quick_actions.review_requests.count'));
        $this->assertEquals(1, $response->json('data.quick_actions.center_support.count'));
    }

    public function test_landlord_can_search_recent_centers_in_dashboard(): void
    {
        Center::create([
            'name' => 'Alpha Cure',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        Center::create([
            'name' => 'Beta Rehab',
            'phone' => '+201033334444',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/dashboard?search=Alpha');

        $response->assertStatus(200);
        $recentCenters = $response->json('data.recent_centers');
        $this->assertCount(1, $recentCenters);
        $this->assertEquals('Alpha Cure', $recentCenters[0]['name']);
    }
}
