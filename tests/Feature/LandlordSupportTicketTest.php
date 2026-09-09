<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlordUser;
    protected Center $centerA;
    protected User $adminUserA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->landlordUser = User::create([
            'name' => 'Landlord Main Admin',
            'phone' => '+201099998888',
            'email' => 'landlord@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'center_id' => null,
        ]);
        $this->landlordUser->assignRole('landlord');

        $this->centerA = Center::create([
            'name' => 'Alpha Cure Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->adminUserA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Center Admin Alpha',
            'phone' => '+201011110000',
            'email' => 'admin@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminUserA->assignRole('admin');
    }

    public function test_landlord_can_list_all_support_tickets_across_centers(): void
    {
        SupportTicket::create([
            'center_id' => $this->centerA->id,
            'user_id' => $this->adminUserA->id,
            'subject' => 'Need Feature Support',
            'message' => 'Help with Reverb configuration',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/support-tickets');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.center.name', 'Alpha Cure Center')
            ->assertJsonPath('data.0.sender_user.name', 'Center Admin Alpha')
            ->assertJsonPath('data.0.subject', 'Need Feature Support');
    }

    public function test_landlord_can_reply_to_support_ticket_and_update_status(): void
    {
        $ticket = SupportTicket::create([
            'center_id' => $this->centerA->id,
            'user_id' => $this->adminUserA->id,
            'subject' => 'System Query',
            'message' => 'How to export patient records?',
            'status' => 'open',
        ]);

        $payload = [
            'reply' => 'You can export records from the Patient Management page directly.',
            'status' => 'closed',
        ];

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson("/api/landlord/support-tickets/{$ticket->id}/reply", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reply', 'You can export records from the Patient Management page directly.')
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.replied_by.name', 'Landlord Main Admin');

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'reply' => 'You can export records from the Patient Management page directly.',
            'replied_by' => $this->landlordUser->id,
            'status' => 'closed',
        ]);
    }
}
