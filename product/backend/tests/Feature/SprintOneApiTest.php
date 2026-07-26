<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintOneApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_use_sprint_one_api_surface(): void
    {
        $this->seed();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'hr@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'hr_admin')
            ->assertJsonStructure(['token', 'user', 'organization'])
            ->json();

        $headers = ['Authorization' => 'Bearer '.$login['token']];

        $this->withHeaders($headers)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'hr@example.com');

        $this->withHeaders($headers)
            ->getJson('/api/admin/sources')
            ->assertOk()
            ->assertJsonStructure(['sources']);

        $this->withHeaders($headers)
            ->getJson('/api/admin/settings')
            ->assertOk()
            ->assertJsonPath('settings.assistant_status', 'active');

        $this->withHeaders($headers)
            ->patchJson('/api/admin/settings', [
                'minutes_saved_per_resolved_question' => 7,
            ])
            ->assertOk()
            ->assertJsonPath('settings.minutes_saved_per_resolved_question', 7);

        $question = $this->withHeaders($headers)
            ->postJson('/api/questions', [
                'question_text' => 'What is the remote work policy?',
            ])
            ->assertCreated()
            ->assertJsonPath('question.status', 'unsupported')
            ->assertJsonPath('answer.answer_status', 'fallback')
            ->json();

        $this->withHeaders($headers)
            ->getJson('/api/questions/'.$question['question']['id'])
            ->assertOk()
            ->assertJsonPath('question.id', $question['question']['id']);

        $this->withHeaders($headers)
            ->getJson('/api/admin/metrics/summary')
            ->assertOk()
            ->assertJsonPath('summary.total_questions', 1)
            ->assertJsonPath('summary.unsupported_questions', 1);
    }
}
