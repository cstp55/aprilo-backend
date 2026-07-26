<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Models\Invoice;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SprintFourBillingSystemTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $employee;
    private Organization $organization;
    private OrganizationSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function ($request) {
            return Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Mocked RAG response.']
                            ]
                        ]
                    ]
                ],
                'embedding' => [
                    'values' => array_fill(0, 1536, 0.1)
                ]
            ], 200);
        });

        $this->organization = Organization::create([
            'name' => 'Billing Enterprise',
            'plan' => 'enterprise',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Admin Controller',
            'email' => 'admin-bill@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Owner->value,
            'status' => 'active',
        ]);

        $this->employee = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Simple Staff',
            'email' => 'staff-bill@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Employee->value,
            'status' => 'active',
        ]);

        $this->settings = OrganizationSetting::create([
            'organization_id' => $this->organization->id,
            'assistant_name' => 'BillBot',
            'billing_mode' => 'pay_as_you_go',
            'usage_queries_count' => 10,
            'usage_amount_due' => 0.50,
        ]);
    }

    public function test_admin_can_update_billing_mode(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.settings.billing.update'), [
            'billing_mode' => 'subscription',
        ])->assertRedirect(route('admin.settings', ['tab' => 'pricing']));

        $this->settings->refresh();
        $this->assertEquals('subscription', $this->settings->billing_mode);
    }

    public function test_non_admin_cannot_update_billing_mode(): void
    {
        $this->actingAs($this->employee);

        $this->post(route('admin.settings.billing.update'), [
            'billing_mode' => 'subscription',
        ])->assertStatus(403);
    }

    public function test_close_billing_cycle_resets_counters_and_creates_invoice(): void
    {
        $this->actingAs($this->admin);

        // Pre-assert current settings values
        $this->assertEquals(10, $this->settings->usage_queries_count);
        $this->assertEquals(0.50, $this->settings->usage_amount_due);

        $this->post(route('admin.settings.billing.close-cycle'))
            ->assertRedirect(route('admin.settings', ['tab' => 'pricing']));

        // Verify settings are reset
        $this->settings->refresh();
        $this->assertEquals(0, $this->settings->usage_queries_count);
        $this->assertEquals(0.00, $this->settings->usage_amount_due);

        // Verify invoice was created in database
        $invoice = Invoice::where('organization_id', $this->organization->id)->firstOrFail();
        $this->assertEquals('pay_as_you_go', $invoice->billing_mode);
        $this->assertEquals(0.50, $invoice->amount);
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->invoice_number);
    }

    public function test_admin_can_view_invoice_pdf_print(): void
    {
        $this->actingAs($this->admin);

        $invoice = Invoice::create([
            'organization_id' => $this->organization->id,
            'invoice_number' => 'INV-TEST-999',
            'billing_mode' => 'pay_as_you_go',
            'amount' => 12.50,
            'status' => 'paid',
            'billing_period_start' => now()->subMonth(),
            'billing_period_end' => now(),
            'due_date' => now()->addDays(5),
            'paid_at' => now(),
        ]);

        $response = $this->get(route('admin.settings.invoices.show', $invoice->id))
            ->assertOk()
            ->assertViewIs('admin.invoice');

        $response->assertSee('INV-TEST-999');
        $response->assertSee('$12.50');
        $response->assertSee('Pay-As-You-Go');
    }

    public function test_unauthorized_org_cannot_view_invoice(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Stranger Org',
            'plan' => 'basic',
            'status' => 'active',
        ]);
        $otherAdmin = User::create([
            'organization_id' => $otherOrg->id,
            'name' => 'Other Admin',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Owner->value,
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'organization_id' => $this->organization->id,
            'invoice_number' => 'INV-SECRET-007',
            'billing_mode' => 'subscription',
            'amount' => 199.00,
            'status' => 'paid',
            'billing_period_start' => now()->subMonth(),
            'billing_period_end' => now(),
            'due_date' => now()->addDays(5),
            'paid_at' => now(),
        ]);

        $this->actingAs($otherAdmin);
        $this->get(route('admin.settings.invoices.show', $invoice->id))
            ->assertStatus(404);
    }

    public function test_question_store_increments_pay_as_you_go_billing_usage(): void
    {
        $this->actingAs($this->employee);

        $this->assertEquals(10, $this->settings->usage_queries_count);
        $this->assertEquals(0.50, $this->settings->usage_amount_due);

        // pose a real question endpoint
        $this->postJson('/api/questions', [
            'question_text' => 'What is the leave policy for new employees?',
        ])->assertCreated();

        $this->settings->refresh();
        $this->assertEquals(11, $this->settings->usage_queries_count);
        $this->assertEquals(0.55, $this->settings->usage_amount_due);
    }
}
