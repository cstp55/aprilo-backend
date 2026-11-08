<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialLifecycleReminderMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public User $user,
        public string $stage = 'day_27', // 'day_3', 'day_10', 'day_27', 'day_30'
        public int $daysRemaining = 3,
        public int $conversationsCount = 0
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->stage) {
            'day_3' => "How is your Aprilo Chatbot performing, {$this->user->name}?",
            'day_10' => "Tip: Enable WhatsApp Direct Connect on your Aprilo Widget",
            'day_27' => "⏳ Your Aprilo 30-Day Trial expires in {$this->daysRemaining} days",
            'day_30' => "Your Aprilo Chatbot trial has concluded — Keep your assistant active",
            default => "Update on your Aprilo Chatbot Free Trial",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-lifecycle',
            with: [
                'orgName' => $this->organization->name,
                'userName' => $this->user->name,
                'stage' => $this->stage,
                'daysRemaining' => $this->daysRemaining,
                'conversationsCount' => $this->conversationsCount,
                'upgradeUrl' => url('/admin/invoices'),
                'widgetUrl' => url('/admin/super/organizations/' . $this->organization->id . '/widget'),
                'supportWhatsApp' => 'https://wa.me/message/BE3K4RAO24GXK1',
            ]
        );
    }
}
