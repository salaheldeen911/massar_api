<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoleRoutesTest extends TestCase
{
    public function test_landlord_route_is_accessible(): void
    {
        $response = $this->getJson('/api/landlord');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Landlord API Service']);
    }

    public function test_business_route_is_accessible(): void
    {
        $response = $this->getJson('/api/business');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Business API Service']);
    }

    public function test_patient_route_is_accessible(): void
    {
        $response = $this->getJson('/api/patient');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Patient API Service']);
    }
}
