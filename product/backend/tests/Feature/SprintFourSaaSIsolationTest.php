<?php

namespace Tests\Feature;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SprintFourSaaSIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_settings_chat_preview_and_saas_tenant_isolation(): void
    {
        // 1. Mock HTTP calls to Gemini API
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
                                    ['text' => 'According to Org A policy, the remote work schedule is 3 days a week.']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }

            return Http::response(['mocked' => true], 200);
        });

        // 2. Setup Tenants (Org A and Org B)
        $orgA = Organization::create([
            'name' => 'Organization A',
            'status' => 'active',
        ]);

        $orgB = Organization::create([
            'name' => 'Organization B',
            'status' => 'active',
        ]);

        OrganizationSetting::create([
            'organization_id' => $orgA->id,
            'assistant_name' => 'Org A Bot',
        ]);

        OrganizationSetting::create([
            'organization_id' => $orgB->id,
            'assistant_name' => 'Org B Bot',
        ]);

        // 3. Create Admin Users for each tenant
        $adminA = User::create([
            'organization_id' => $orgA->id,
            'name' => 'Admin A',
            'email' => 'adminA@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::HrAdmin->value,
            'status' => 'active',
        ]);

        $adminB = User::create([
            'organization_id' => $orgB->id,
            'name' => 'Admin B',
            'email' => 'adminB@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::HrAdmin->value,
            'status' => 'active',
        ]);

        // 4. Create an indexed source and chunk for Org A only
        $sourceA = KnowledgeSource::create([
            'organization_id' => $orgA->id,
            'title' => 'Org A Remote Work Policy',
            'source_type' => 'txt',
            'file_path' => 'sources/org-a.txt',
            'status' => 'indexed',
            'access_scope' => 'all_employees',
            'uploaded_by' => $adminA->id,
        ]);

        $chunkA = KnowledgeChunk::create([
            'organization_id' => $orgA->id,
            'source_id' => $sourceA->id,
            'chunk_text' => 'According to Org A policy, the remote work schedule is 3 days a week.',
            'chunk_index' => 0,
            'embedding' => array_fill(0, 1536, 0.15),
        ]);

        // 5. Test Live Preview Endpoint for Org A (Admin A)
        // Login as Admin A
        $this->actingAs($adminA);

        $responseA = $this->postJson(route('admin.settings.preview'), [
            'question_text' => 'What is the remote work policy?',
        ])->assertOk();

        // Admin A should get the answer and see Org A's source cited
        $dataA = $responseA->json();
        $this->assertStringContainsString('remote work schedule is 3 days', $dataA['answer_text']);
        $this->assertCount(1, $dataA['sources']);
        $this->assertEquals('Org A Remote Work Policy', $dataA['sources'][0]['title']);

        // 6. Test Live Preview Endpoint for Org B (Admin B) - SaaS Isolation Verification
        // Login as Admin B
        $this->actingAs($adminB);

        // Mock HTTP calls for Org B completion to simulate default/un-grounded answer
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
                                    ['text' => 'I cannot find any approved documents regarding remote work in Organization B.']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }

            return Http::response(['mocked' => true], 200);
        });

        $responseB = $this->postJson(route('admin.settings.preview'), [
            'question_text' => 'What is the remote work policy?',
        ])->assertOk();

        // Admin B should NOT see Org A's chunk because of strict organization_id scoping
        $dataB = $responseB->json();
        $this->assertCount(0, $dataB['sources']);
        $this->assertStringContainsString('I cannot confirm this from approved sources yet', $dataB['answer_text']);
    }

    public function test_role_based_access_isolation_within_tenant(): void
    {
        // 1. Mock HTTP
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
                                    ['text' => 'The salary range for Grade A is $100k-$150k.']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }

            return Http::response(['mocked' => true], 200);
        });

        // 2. Setup Tenant and Users
        $org = Organization::create([
            'name' => 'Isolation Org',
            'status' => 'active',
        ]);

        $admin = User::create([
            'organization_id' => $org->id,
            'name' => 'HR Admin',
            'email' => 'admin-iso@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::HrAdmin->value,
            'status' => 'active',
        ]);

        $employee = User::create([
            'organization_id' => $org->id,
            'name' => 'Standard Employee',
            'email' => 'emp-iso@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Employee->value,
            'status' => 'active',
        ]);

        // 3. Create an hr_only source & chunk
        $source = KnowledgeSource::create([
            'organization_id' => $org->id,
            'title' => 'Executive Compensation Guide',
            'source_type' => 'txt',
            'file_path' => 'sources/comp.txt',
            'status' => 'indexed',
            'access_scope' => 'hr_only',
            'uploaded_by' => $admin->id,
        ]);

        $chunk = KnowledgeChunk::create([
            'organization_id' => $org->id,
            'source_id' => $source->id,
            'chunk_text' => 'The salary range for Grade A is $100k-$150k.',
            'chunk_index' => 0,
            'embedding' => array_fill(0, 1536, 0.15),
        ]);

        // 4. Admin queries using preview endpoint
        $this->actingAs($admin);
        $adminResponse = $this->postJson(route('admin.settings.preview'), [
            'question_text' => 'What is the salary range for Grade A?',
        ])->assertOk()->json();

        // Admin has HR access scope, so chunk should be retrieved and cited
        $this->assertCount(1, $adminResponse['sources']);
        $this->assertEquals('Executive Compensation Guide', $adminResponse['sources'][0]['title']);

        // 5. Standard employee queries using standard API
        $this->actingAs($employee);
        
        // Re-mock to return default answer for employee (since no context should be retrieved)
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
                                    ['text' => 'I cannot answer this question.']
                                ]
                            ]
                        ]
                    ]
                ], 200);
            }

            return Http::response(['mocked' => true], 200);
        });

        $employeeResponse = $this->postJson('/api/questions', [
            'question_text' => 'What is the salary range for Grade A?',
        ])->assertCreated()->json();

        // Standard employee does NOT have access, so sources list should be empty
        $this->assertCount(0, $employeeResponse['answer']['sources']);
    }
}
