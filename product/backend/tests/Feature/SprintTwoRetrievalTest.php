<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

use Illuminate\Support\Facades\Http;

class SprintTwoRetrievalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_indexed_source_can_ground_an_answer_with_citations(): void
    {
        Http::fake([
            '*/embedContent*' => Http::response([
                'embedding' => [
                    'values' => array_fill(0, 1536, 0.15)
                ]
            ]),
            '*/generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Based on approved HR sources, employees may work remotely up to three days per week [1].']
                            ]
                        ]
                    ]
                ]
            ])
        ]);

        $this->seed();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'hr@example.com',
            'password' => 'password',
        ])->assertOk()->json();

        $headers = ['Authorization' => 'Bearer '.$login['token']];
        $file = UploadedFile::fake()->createWithContent(
            'employee-handbook.txt',
            "Remote Work Policy\n\nEmployees may work remotely up to three days per week with manager approval. Remote work must be recorded in the HR system.\n\nExpense Policy\n\nApproved business expenses are reimbursed after receipt submission."
        );

        $this->withHeaders($headers)
            ->post('/api/admin/sources', [
                'title' => 'Employee Handbook',
                'access_scope' => 'all_employees',
                'file' => $file,
            ])
            ->assertCreated()
            ->assertJsonPath('source.status', 'indexed')
            ->assertJsonPath('source.chunks_count', 1);

        $answer = $this->withHeaders($headers)
            ->postJson('/api/questions', [
                'question_text' => 'How many days can employees work remotely?',
            ])
            ->assertCreated()
            ->assertJsonPath('question.status', 'answered')
            ->assertJsonPath('answer.answer_status', 'answered')
            ->assertJsonCount(1, 'answer.sources')
            ->json('answer');

        $this->assertStringContainsString('three days per week', $answer['answer_text']);
        $this->assertSame('[1]', $answer['sources'][0]['citation_label']);
        $this->assertSame('Employee Handbook', $answer['sources'][0]['source']['title']);
    }
}
