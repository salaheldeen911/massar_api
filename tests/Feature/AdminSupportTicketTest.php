<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected Center $centerA;
    protected Center $centerB;
    protected User $adminUserA;
    protected User $adminUserB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->centerA = Center::create([
            'name' => 'Alpha Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->adminUserA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Admin Alpha',
            'phone' => '+201011110000',
            'email' => 'admin@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminUserA->assignRole('admin');

        $this->centerB = Center::create([
            'name' => 'Beta Center',
            'phone' => '+201033334444',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $this->adminUserB = User::create([
            'center_id' => $this->centerB->id,
            'name' => 'Admin Beta',
            'phone' => '+201033330000',
            'email' => 'admin@beta.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminUserB->assignRole('admin');
    }

    public function test_admin_can_submit_support_ticket(): void
    {
        $payload = [
            'subject' => 'Payment Gateway Question',
            'message' => 'We need clarification on integration options.',
        ];

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson('/api/business/support-tickets', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.subject', 'Payment Gateway Question')
            ->assertJsonPath('data.message', 'We need clarification on integration options.')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.center_id', $this->centerA->id)
            ->assertJsonPath('data.user_id', $this->adminUserA->id);

        $this->assertDatabaseHas('support_tickets', [
            'center_id' => $this->centerA->id,
            'user_id' => $this->adminUserA->id,
            'subject' => 'Payment Gateway Question',
            'status' => 'open',
        ]);
    }

    public function test_admin_can_list_only_own_center_tickets(): void
    {
        SupportTicket::create([
            'center_id' => $this->centerA->id,
            'user_id' => $this->adminUserA->id,
            'subject' => 'Alpha Ticket',
            'message' => 'Alpha issue body',
            'status' => 'open',
        ]);

        SupportTicket::create([
            'center_id' => $this->centerB->id,
            'user_id' => $this->adminUserB->id,
            'subject' => 'Beta Ticket',
            'message' => 'Beta issue body',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson('/api/business/support-tickets');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.subject', 'Alpha Ticket');
    }

    public function test_admin_cannot_view_another_center_ticket_details(): void
    {
        $ticketB = SupportTicket::create([
            'center_id' => $this->centerB->id,
            'user_id' => $this->adminUserB->id,
            'subject' => 'Beta Ticket Details',
            'message' => 'Beta details message',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson("/api/business/support-tickets/{$ticketB->id}");

        $response->assertStatus(404);
    }
}
