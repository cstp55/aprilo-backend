<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ChatbotWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public User $user,
        public ?string $widgetKey = null
    ) {
        $this->widgetKey = $widgetKey ?: ($organization->settings?->public_widget_key ?? 'pk_live_' . substr($organization->id, 0, 8));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Aprilo Chatbot — Your 30-Day Free Trial is Live! 🚀'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chatbot-welcome',
            with: [
                'orgName' => $this->organization->name,
                'userName' => $this->user->name,
                'widgetKey' => $this->widgetKey,
                'setupUrl' => url('/admin/super/organizations/' . $this->organization->id . '/widget'),
                'supportWhatsApp' => 'https://wa.me/message/BE3K4RAO24GXK1',
            ]
        );
    }
}
