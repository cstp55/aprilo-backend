<?php

namespace Tests\Feature\Support;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Product;
use App\Models\Support\SupportCustomer;
use App\Models\Support\SupportOutboxEvent;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\User;
use App\Services\Support\Channels\EmailSupportChannelAdapter;
use App\Services\Support\Channels\WebsiteSupportChannelAdapter;
use App\Services\Support\Channels\WhatsAppSupportChannelAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelAdaptersTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private OrganizationSetting $settings;
    private string $testSecret = 'secret_test_key_123';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.whatsapp.app_secret' => $this->testSecret]);

        $this->organization = Organization::create([
            'name' => 'OmniDesk Telecom',
            'status' => 'active',
        ]);

        $product = Product::create([
            'name' => 'Aprilo Support',
            'slug' => 'aprilo-support',
            'category' => 'support_saas',
            'product_type' => 'subscription',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->organization->products()->attach($product->id, [
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        $this->settings = $this->organization->settings ?? $this->organization->settings()->firstOrCreate([]);
        $this->settings->update([
            'public_widget_key' => 'wg_test_key_9999',
            'whatsapp_phone_number_id' => '1029384756',
            'whatsapp_connected' => true,
            'connect_mail' => true,
        ]);
    }

    public function test_website_inbound_message_creates_ticket_and_comment(): void
    {
        $response = $this->postJson('/api/webhooks/support/website', [
            'widget_key' => 'wg_test_key_9999',
            'conversation_id' => 'conv_web_123',
            'customer_name' => 'Alice Web',
            'customer_email' => 'alice@webclient.com',
            'message' => 'Hello, I need help configuring SAML SSO.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('processed', true);

        $ticket = SupportTicket::first();
        $this->assertNotNull($ticket);
        $this->assertEquals('website', $ticket->channel);
        $this->assertEquals('conv_web_123', $ticket->conversation_id);
        $this->assertStringContainsString('SAML SSO', $ticket->description);

        $comment = SupportTicketComment::first();
        $this->assertNotNull($comment);
        $this->assertFalse($comment->is_internal);
        $this->assertEquals('customer', $comment->author_type);
    }

    public function test_website_inbound_message_deduplication(): void
    {
        $adapter = app(WebsiteSupportChannelAdapter::class);

        $payload = [
            'conversation_id' => 'conv_dedup',
            'customer_id' => 'web_cust_1',
            'customer_name' => 'Bob Web',
            'message' => 'First message',
            'event_id' => 'evt_fixed_id_100',
        ];

        $comment1 = $adapter->processInbound($this->organization, $payload);
        $this->assertNotNull($comment1);

        // Sending same event_id again returns null
        $comment2 = $adapter->processInbound($this->organization, $payload);
        $this->assertNull($comment2);

        $this->assertEquals(1, SupportTicketComment::count());
    }

    public function test_whatsapp_webhook_signature_verification(): void
    {
        $payload = json_encode(['test' => 'ping']);
        $validSignature = 'sha256=' . hash_hmac('sha256', $payload, $this->testSecret);

        // 1. Valid Signature returns 200
        $validRes = $this->call(
            'POST',
            '/api/webhooks/support/whatsapp',
            [],
            [],
            [],
            ['HTTP_X-Hub-Signature-256' => $validSignature, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );
        $validRes->assertStatus(200);

        // 2. Forged Signature returns 401
        $invalidRes = $this->call(
            'POST',
            '/api/webhooks/support/whatsapp',
            [],
            [],
            [],
            ['HTTP_X-Hub-Signature-256' => 'sha256=forged_bad_hash', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );
        $invalidRes->assertStatus(401);
    }

    public function test_whatsapp_inbound_message_creates_customer_and_ticket(): void
    {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => ['phone_number_id' => '1029384756'],
                                'contacts' => [
                                    ['profile' => ['name' => 'Carlos Mobile']],
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.HBgLMTIzNDU2',
                                        'from' => '+15551234567',
                                        'type' => 'text',
                                        'text' => ['body' => 'Can you please check our server reboot status?'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $jsonPayload = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $jsonPayload, $this->testSecret);

        $response = $this->call(
            'POST',
            '/api/webhooks/support/whatsapp',
            [],
            [],
            [],
            ['HTTP_X-Hub-Signature-256' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $jsonPayload
        );

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('processed', true);

        // Verify customer created
        $customer = SupportCustomer::where('phone', '+15551234567')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Carlos Mobile', $customer->name);

        // Verify ticket created
        $ticket = SupportTicket::first();
        $this->assertNotNull($ticket);
        $this->assertEquals('whatsapp', $ticket->channel);
        $this->assertEquals($customer->id, $ticket->customer_id);

        // Verify comment
        $comment = SupportTicketComment::first();
        $this->assertNotNull($comment);
        $this->assertEquals('wamid.HBgLMTIzNDU2', $comment->external_message_id);
        $this->assertFalse($comment->is_internal);
    }

    public function test_whatsapp_deduplication_protects_against_duplicate_events(): void
    {
        $adapter = app(WhatsAppSupportChannelAdapter::class);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'messages' => [
                                    [
                                        'id' => 'wamid.DEDUP_MSG_99',
                                        'from' => '+15559876543',
                                        'type' => 'text',
                                        'text' => ['body' => 'First message delivery'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $res1 = $adapter->processInbound($this->organization, $payload);
        $this->assertNotNull($res1);

        // Second delivery of same message ID
        $res2 = $adapter->processInbound($this->organization, $payload);
        $this->assertNull($res2);

        $this->assertEquals(1, SupportTicketComment::count());
    }

    public function test_email_inbound_creates_ticket_and_normalizes_sender(): void
    {
        $response = $this->postJson('/api/webhooks/support/email', [
            'sender' => 'customer.help@partner.org',
            'sender_name' => 'Dr. Diane Partner',
            'subject' => 'Emergency: Cluster Node Failover',
            'body' => 'Primary database replica dropped connection at 16:45 UTC.',
            'message_id' => '<msg_initial_1234@partner.org>',
            'organization_id' => $this->organization->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('processed', true);

        $customer = SupportCustomer::where('email', 'customer.help@partner.org')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Dr. Diane Partner', $customer->name);

        $ticket = SupportTicket::first();
        $this->assertNotNull($ticket);
        $this->assertEquals('email', $ticket->channel);
        $this->assertEquals('Emergency: Cluster Node Failover', $ticket->subject);
    }

    public function test_email_inbound_threads_into_existing_ticket_via_subject_or_in_reply_to(): void
    {
        $adapter = app(EmailSupportChannelAdapter::class);

        // 1. Initial Email creates ticket TIC-1001
        $c1 = $adapter->processInbound($this->organization, [
            'sender' => 'user@enterprise.com',
            'subject' => 'Initial inquiry',
            'body' => 'Need instructions for SSL.',
            'message_id' => '<thread_root_100@enterprise.com>',
        ]);
        $this->assertNotNull($c1);
        $ticket = $c1->ticket;

        // 2. Follow-up email references the ticket in subject [TIC-1001]
        $c2 = $adapter->processInbound($this->organization, [
            'sender' => 'user@enterprise.com',
            'subject' => "[{$ticket->ticket_number}] Re: Initial inquiry",
            'body' => 'Thanks, also which port is required?',
            'message_id' => '<thread_reply_101@enterprise.com>',
            'in_reply_to' => '<thread_root_100@enterprise.com>',
        ]);

        $this->assertNotNull($c2);
        // Must attach to the SAME ticket
        $this->assertEquals($ticket->id, $c2->ticket_id);
        $this->assertEquals(1, SupportTicket::count());
        $this->assertEquals(2, $ticket->comments()->count());
    }

    public function test_outbound_channel_dispatch_creates_outbox_events_and_never_sends_internal_notes(): void
    {
        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Test email dispatch',
            'description' => 'Test details',
            'channel' => 'email',
            'status' => 'open',
        ]);

        $customer = SupportCustomer::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Customer',
            'email' => 'client@test.com',
        ]);
        $ticket->update(['customer_id' => $customer->id]);

        $emailAdapter = app(EmailSupportChannelAdapter::class);

        // 1. Internal Note must NEVER be sent
        $internalNote = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'organization_id' => $this->organization->id,
            'body' => 'Secret internal note for agents only',
            'is_internal' => true,
        ]);

        $internalSent = $emailAdapter->dispatchOutbound($internalNote);
        $this->assertFalse($internalSent, 'Safety violation: Internal note was dispatched outbound!');
        $this->assertEquals(0, SupportOutboxEvent::count());

        // 2. Customer-visible reply creates outbox record
        $publicReply = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'organization_id' => $this->organization->id,
            'body' => 'Public customer reply',
            'is_internal' => false,
        ]);

        $publicSent = $emailAdapter->dispatchOutbound($publicReply);
        $this->assertTrue($publicSent);

        $outbox = SupportOutboxEvent::first();
        $this->assertNotNull($outbox);
        $this->assertEquals('email', $outbox->channel);
        $this->assertEquals('client@test.com', $outbox->recipient);
        $this->assertEquals('sent', $outbox->status);
    }
}
