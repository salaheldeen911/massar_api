<?php

namespace Tests\Unit;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_helpers_return_null_when_unauthenticated(): void
    {
        $this->assertNull(currentUser());
        $this->assertNull(currentCenterId());
        $this->assertNull(currentCenter());
        $this->assertFalse(isLandlord());
    }

    public function test_helpers_return_correct_center_and_role_info(): void
    {
        $this->seed(RoleSeeder::class);

        $center = Center::create([
            'name' => 'Healing Center',
            'phone' => '+201000000000',
            'specialty' => 'Physical Therapy',
            'city' => 'Cairo',
        ]);

        $user = User::create([
            'name' => 'Admin User',
            'phone' => '+201000000001',
            'email' => 'admin@healing.com',
            'password' => bcrypt('password'),
            'center_id' => $center->id,
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $this->assertEquals($user->id, currentUser()->id);
        $this->assertEquals($center->id, currentCenterId());
        $this->assertEquals($center->id, currentCenter()->id);
        $this->assertFalse(isLandlord());

        $landlord = User::create([
            'name' => 'Global Landlord',
            'phone' => '+201000000002',
            'email' => 'landlord@massar.com',
            'password' => bcrypt('password'),
            'center_id' => null,
        ]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);

        $this->assertTrue(isLandlord());
        $this->assertNull(currentCenterId());
        $this->assertNull(currentCenter());
    }
}
