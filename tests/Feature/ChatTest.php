<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Center;
use App\Models\ChatMessage;
use App\Models\PatientProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected Center $centerA;
    protected Center $centerB;
    protected User $therapistA;
    protected User $patientA;
    protected User $patientB;
    protected User $otherTherapistA;
    protected User $adminA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        // Center A
        $this->centerA = Center::create([
            'name' => 'Massar Center A',
            'phone' => '+201011111111',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        // Center B
        $this->centerB = Center::create([
            'name' => 'Massar Center B',
            'phone' => '+201022222222',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        // Therapist A in Center A
        $this->therapistA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Ahmed (Center A)',
            'phone' => '+201012345671',
            'email' => 'therapistA@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapistA->assignRole('therapist');

        // Admin A in Center A
        $this->adminA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Admin Ali (Center A)',
            'phone' => '+201012345672',
            'email' => 'adminA@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminA->assignRole('admin');

        // Other Therapist A in Center A
        $this->otherTherapistA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Hassan (Center A)',
            'phone' => '+201012345673',
            'email' => 'otherTherapistA@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->otherTherapistA->assignRole('therapist');

        // Patient A assigned to Therapist A in Center A
        $this->patientA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Patient A (Center A)',
            'phone' => '+201087654321',
            'email' => 'patientA@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patientA->assignRole('patient');

        PatientProfile::create([
            'center_id' => $this->centerA->id,
            'user_id' => $this->patientA->id,
            'therapist_id' => $this->therapistA->id,
            'birth_date' => '1995-01-01',
        ]);

        // Patient B in Center B
        $this->patientB = User::create([
            'center_id' => $this->centerB->id,
            'name' => 'Patient B (Center B)',
            'phone' => '+201087654322',
            'email' => 'patientB@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->patientB->assignRole('patient');

        PatientProfile::create([
            'center_id' => $this->centerB->id,
            'user_id' => $this->patientB->id,
            'therapist_id' => null,
            'birth_date' => '1996-01-01',
        ]);
    }

    public function test_therapist_can_send_message_to_assigned_patient_and_dispatches_event(): void
    {
        Event::fake();

        $response = $this->actingAs($this->therapistA, 'sanctum')
            ->postJson('/api/business/chat/messages', [
                'receiver_id' => $this->patientA->id,
                'message' => 'Hello patient, how is your knee today?',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sender_id', $this->therapistA->id)
            ->assertJsonPath('data.receiver_id', $this->patientA->id)
            ->assertJsonPath('data.message', 'Hello patient, how is your knee today?');

        $this->assertDatabaseHas('chat_messages', [
            'center_id' => $this->centerA->id,
            'sender_id' => $this->therapistA->id,
            'receiver_id' => $this->patientA->id,
            'message' => 'Hello patient, how is your knee today?',
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    public function test_patient_can_send_message_to_assigned_therapist(): void
    {
        Event::fake();

        $response = $this->actingAs($this->patientA, 'sanctum')
            ->postJson('/api/patient/chat/messages', [
                'message' => 'Hello doctor, I completed today\'s exercises.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sender_id', $this->patientA->id)
            ->assertJsonPath('data.receiver_id', $this->therapistA->id);

        $this->assertDatabaseHas('chat_messages', [
            'center_id' => $this->centerA->id,
            'sender_id' => $this->patientA->id,
            'receiver_id' => $this->therapistA->id,
        ]);
    }

    public function test_center_isolation_blocks_messaging_across_different_centers(): void
    {
        // Therapist A (Center A) trying to message Patient B (Center B)
        $response = $this->actingAs($this->therapistA, 'sanctum')
            ->postJson('/api/business/chat/messages', [
                'receiver_id' => $this->patientB->id,
                'message' => 'Cross center test message',
            ]);

        $response->assertStatus(403);
    }

    public function test_unassigned_therapist_in_same_center_is_blocked_from_messaging_patient(): void
    {
        // OtherTherapistA (Center A) trying to message Patient A who is assigned to Therapist A
        $response = $this->actingAs($this->otherTherapistA, 'sanctum')
            ->postJson('/api/business/chat/messages', [
                'receiver_id' => $this->patientA->id,
                'message' => 'Unauthorized message',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_be_assigned_and_chat_with_patient(): void
    {
        // Assign Admin A as caregiver for Patient A
        $this->patientA->patientProfile->update(['therapist_id' => $this->adminA->id]);

        $response = $this->actingAs($this->adminA, 'sanctum')
            ->postJson('/api/business/chat/messages', [
                'receiver_id' => $this->patientA->id,
                'message' => 'Hello from Admin caregiver',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sender_id', $this->adminA->id);
    }

    public function test_chat_with_media_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test_attachment.jpg');

        $response = $this->actingAs($this->therapistA, 'sanctum')
            ->postJson('/api/business/chat/messages', [
                'receiver_id' => $this->patientA->id,
                'message' => 'Check this image',
                'attachment' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Check this image');

        $this->assertNotNull($response->json('data.attachment_url'));
    }

    public function test_therapist_can_list_conversations_and_mark_messages_as_read(): void
    {
        // Patient sends message to Therapist
        ChatMessage::create([
            'center_id' => $this->centerA->id,
            'sender_id' => $this->patientA->id,
            'receiver_id' => $this->therapistA->id,
            'message' => 'Need help with exercise #2',
            'created_at' => now(),
        ]);

        // 1. Therapist gets conversations list
        $conversationsResponse = $this->actingAs($this->therapistA, 'sanctum')
            ->getJson('/api/business/chat/conversations');

        $conversationsResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.patient.id', $this->patientA->id)
            ->assertJsonPath('data.0.unread_count', 1)
            ->assertJsonPath('data.0.last_message', 'Need help with exercise #2');

        // 2. Therapist views message history (automatically marks as read)
        $messagesResponse = $this->actingAs($this->therapistA, 'sanctum')
            ->getJson("/api/business/chat/messages/{$this->patientA->id}");

        $messagesResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.message', 'Need help with exercise #2');

        // 3. Verify unread count is now 0
        $updatedConversationsResponse = $this->actingAs($this->therapistA, 'sanctum')
            ->getJson('/api/business/chat/conversations');

        $updatedConversationsResponse->assertStatus(200)
            ->assertJsonPath('data.0.unread_count', 0);
    }
}
