<?php

namespace Tests\Feature;

use App\Models\KnowledgeSource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_hr_admin_can_use_laravel_admin_console(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'embedContent')) {
                return Http::response([
                    'embedding' => [
                        'values' => array_fill(0, 1536, 0.15)
                    ]
                ], 200);
            }
            return Http::response(['mocked' => true], 200);
        });

        $this->seed();

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Aprilo AI');

        $this->post('/admin/login', [
            'email' => 'hr@example.com',
            'password' => 'password',
        ])->assertRedirect('/admin/leaves');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Admin Dashboard');

        $file = UploadedFile::fake()->createWithContent(
            'employee-handbook.txt',
            'Remote work is allowed up to three days per week with manager approval.'
        );

        $this->post('/admin/sources', [
            'title' => 'Employee Handbook',
            'access_scope' => 'all_employees',
            'file' => $file,
        ])->assertRedirect('/admin/sources');

        $this->assertDatabaseHas('knowledge_sources', [
            'title' => 'Employee Handbook',
            'status' => 'indexed',
        ]);

        $source = KnowledgeSource::where('title', 'Employee Handbook')->firstOrFail();
        $this->assertSame(1, $source->chunks()->count());

        $this->get('/admin/sources')
            ->assertOk()
            ->assertSee('Employee Handbook');
    }
}
