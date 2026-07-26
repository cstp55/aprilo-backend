<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_validate_identity_and_retrieve_profile_details(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->first();
        
        $this->assertEquals('employee@example.com', $employee->email);

        // Login as employee
        $login = $this->postJson('/api/auth/login', [
            'email' => 'employee@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->json();

        $token = $login['token'];
        $headers = ['Authorization' => 'Bearer ' . $token];

        // Access /api/me before validation
        $response = $this->withHeaders($headers)
            ->getJson('/api/me')
            ->assertOk()
            ->json();

        // Check if employee payload contains initial stats
        $this->assertEquals('EMP-2026-987', $response['user']['employee_id']);
        $this->assertArrayHasKey('joining_date', $response['user']);
        $this->assertArrayHasKey('id_card', $response['user']);
        $this->assertNotNull($response['user']['id_card']);
        $this->assertEquals('APR-98765-EMP', $response['user']['id_card']['card_number']);

        // Post invalid Employee ID
        $this->withHeaders($headers)
            ->postJson('/api/auth/validate-employee', [
                'employee_id' => 'EMP-INVALID',
            ])
            ->assertStatus(422);

        // Post correct Employee ID
        $this->withHeaders($headers)
            ->postJson('/api/auth/validate-employee', [
                'employee_id' => 'EMP-2026-987',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['user' => ['employee_id', 'joining_date', 'id_card', 'leave_requests_count', 'wfh_requests_count', 'total_questions_count']]);
    }
}
