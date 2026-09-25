<?php

namespace App\Mail;

use App\Models\OnboardingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OnboardingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public OnboardingRequest $onboarding)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Aprilo organization is ready');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.onboarding-confirmation');
    }
}
