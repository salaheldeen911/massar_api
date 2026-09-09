<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Exercise;
use App\Models\PatientProfile;
use App\Models\TherapistProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Center $centerA;
    protected User $adminUserA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->centerA = Center::create([
            'name' => 'Center Alpha',
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
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->getJson('/api/business/dashboard');

        $response->assertStatus(401);
    }

    public function test_patient_user_cannot_access_business_dashboard(): void
    {
        $patientUser = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Patient User',
            'phone' => '+201022223333',
            'email' => 'patient@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser->assignRole('patient');

        $response = $this->actingAs($patientUser, 'sanctum')
            ->getJson('/api/business/dashboard');

        $response->assertStatus(403);
    }

    public function test_therapist_can_access_business_dashboard_with_therapist_scoped_data(): void
    {
        $therapist = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Therapist User',
            'phone' => '+201022223333',
            'email' => 'therapist@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');

        $response = $this->actingAs($therapist, 'sanctum')
            ->getJson('/api/business/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'therapist');
    }

    public function test_admin_can_get_dashboard_data_scoped_to_their_center(): void
    {
        $therapist = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Ahmed Ali',
            'phone' => '+201012345678',
            'email' => 'ahmed@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist->id,
            'specialization' => 'Physical Therapy',
        ]);

        $patientUser = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Mohamed Hassan',
            'phone' => '+201087654321',
            'email' => 'mohamed@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->centerA->id,
            'user_id' => $patientUser->id,
            'therapist_id' => $therapist->id,
            'birth_date' => '1995-05-15',
            'current_week' => 4,
        ]);

        Exercise::create([
            'center_id' => $this->centerA->id,
            'title' => 'Shoulder Stretch',
            'default_sets' => 3,
            'default_repeats' => 10,
        ]);

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson('/api/business/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'stats' => [
                        'total_patients' => ['count', 'change_percentage', 'period'],
                        'total_therapists' => ['count', 'change_percentage', 'period'],
                        'total_exercises' => ['count', 'change_percentage', 'period'],
                    ],
                    'recent_therapists',
                    'recent_patients',
                ],
            ]);

        $this->assertEquals(1, $response->json('data.stats.total_patients.count'));
        $this->assertEquals(1, $response->json('data.stats.total_therapists.count'));
        $this->assertEquals(1, $response->json('data.stats.total_exercises.count'));

        $this->assertCount(1, $response->json('data.recent_therapists'));
        $this->assertEquals('Ahmed Ali', $response->json('data.recent_therapists.0.name'));
        $this->assertEquals(1, $response->json('data.recent_therapists.0.assigned_patients_count'));

        $this->assertCount(1, $response->json('data.recent_patients'));
        $this->assertEquals('Mohamed Hassan', $response->json('data.recent_patients.0.name'));
        $this->assertEquals(4, $response->json('data.recent_patients.0.current_week'));
    }

    public function test_data_isolation_between_different_centers(): void
    {
        $centerB = Center::create([
            'name' => 'Center Beta',
            'phone' => '+201099998888',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        User::create([
            'center_id' => $centerB->id,
            'name' => 'Therapist Beta',
            'phone' => '+201055554444',
            'email' => 'therapist@beta.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ])->assignRole('therapist');

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson('/api/business/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.stats.total_therapists.count'));
        $this->assertCount(0, $response->json('data.recent_therapists'));
    }
}
