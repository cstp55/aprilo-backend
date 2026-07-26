<?php

namespace Tests\Feature;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Services\AI\EmbeddingService;
use App\Services\Knowledge\RetrievalService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SprintThreeHybridSearchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_hybrid_search_scoring_and_retrieval(): void
    {
        // 1. Mock HTTP requests to Gemini APIs
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'embedContent')) {
                return Http::response([
                    'embedding' => [
                        'values' => array_fill(0, 1536, 0.15)
                    ]
                ], 200);
            }

            if (str_contains($request->url(), 'generateContent')) {
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'According to the Expense Policy, business expenses are reimbursed after receipt submission [1].']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }

            return Http::response(['mocked' => true], 200);
        });

        $this->seed();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'hr@example.com',
            'password' => 'password',
        ])->assertOk()->json();

        $headers = ['Authorization' => 'Bearer '.$login['token']];
        
        // 2. Upload file which dispatches ProcessKnowledgeSource sync
        $file = UploadedFile::fake()->createWithContent(
            'expense-policy.txt',
            "Expense Policy\n\nApproved business expenses are reimbursed after receipt submission."
        );

        $this->withHeaders($headers)
            ->post('/api/admin/sources', [
                'title' => 'Expense Policy',
                'access_scope' => 'all_employees',
                'file' => $file,
            ])
            ->assertCreated();

        // 3. Confirm chunks and embeddings were created
        $source = KnowledgeSource::where('title', 'Expense Policy')->first();
        $this->assertNotNull($source);
        $this->assertSame('indexed', $source->status);
        $this->assertGreaterThan(0, $source->chunks()->count());

        $chunk = $source->chunks()->first();
        $this->assertNotNull($chunk->embedding);
        $this->assertCount(1536, $chunk->embedding);

        // 4. Verify hybrid retrieval
        $answerResponse = $this->withHeaders($headers)
            ->postJson('/api/questions', [
                'question_text' => 'How can I get my business expenses reimbursed?',
            ])
            ->assertCreated()
            ->json();

        $this->assertSame('answered', $answerResponse['answer']['answer_status']);
        $this->assertStringContainsString('business expenses are reimbursed', $answerResponse['answer']['answer_text']);
        $this->assertCount(1, $answerResponse['answer']['sources']);
        $this->assertSame('Expense Policy', $answerResponse['answer']['sources'][0]['source']['title']);
    }
}
