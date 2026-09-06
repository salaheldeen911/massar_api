<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_creates_all_four_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $expectedRoles = ['landlord', 'admin', 'therapist', 'patient'];

        foreach ($expectedRoles as $roleName) {
            $this->assertDatabaseHas('roles', [
                'name' => $roleName,
                'guard_name' => 'sanctum',
            ]);
            $this->assertDatabaseHas('roles', [
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }
}
