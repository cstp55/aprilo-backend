<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadCapturedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $orgName,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public string $initialMessage,
        public string $pageUrl = '',
        public string $pageTitle = '',
        public string $conversationId = ''
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = "New Lead Captured: {$this->customerName} - {$this->orgName}";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->buildHtml());
    }

    private function buildHtml(): string
    {
        $safeOrg = htmlspecialchars($this->orgName, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($this->customerName, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($this->customerEmail ?: 'Not provided', ENT_QUOTES, 'UTF-8');
        $safePhone = htmlspecialchars($this->customerPhone ?: 'Not provided', ENT_QUOTES, 'UTF-8');
        $safeMsg = nl2br(htmlspecialchars($this->initialMessage, ENT_QUOTES, 'UTF-8'));
        $safePage = htmlspecialchars($this->pageUrl ?: 'Direct embed', ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($this->pageTitle ?: 'Website Chat', ENT_QUOTES, 'UTF-8');
        $now = now()->toDayDateTimeString();

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>New Lead Captured</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
  <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 24px; color: #ffffff;">
      <span style="display: inline-block; background: #22c55e; color: #ffffff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 8px; border-radius: 4px; margin-bottom: 8px;">New Inbound Lead</span>
      <h1 style="margin: 0; font-size: 20px; font-weight: 700;">{$safeName}</h1>
      <p style="margin: 4px 0 0; font-size: 13px; color: #94a3b8;">Captured via {$safeOrg} Public Chat Widget</p>
    </div>

    <div style="padding: 24px;">
      <h3 style="margin: 0 0 16px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Contact Information</h3>
      
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px; width: 140px;">Full Name</td>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px; font-weight: 600;">{$safeName}</td>
        </tr>
        <tr>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Email Address</td>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px; font-weight: 600;"><a href="mailto:{$safeEmail}" style="color: #2563eb; text-decoration: none;">{$safeEmail}</a></td>
        </tr>
        <tr>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Phone Number</td>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px; font-weight: 600;">{$safePhone}</td>
        </tr>
        <tr>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Page / Topic</td>
          <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{$safeTitle}</td>
        </tr>
        <tr>
          <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Captured At</td>
          <td style="padding: 10px 0; color: #0f172a; font-size: 13px;">{$now}</td>
        </tr>
      </table>

      <h3 style="margin: 0 0 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Customer Inquiry</h3>
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
        {$safeMsg}
      </div>

      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; text-align: center;">
        This automated lead notification was delivered by Aprilo Support AI for {$safeOrg}.
      </div>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
