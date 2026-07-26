<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WidgetAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $replySubject,
        public string $bodyText,
        public array $sources = [],
        public bool $isEscalated = false,
        public string $senderName = 'Aprilo Assistant'
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->replySubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    /**
     * Build a beautiful premium HTML email body.
     */
    private function buildHtml(): string
    {
        $citationsHtml = '';
        if (!empty($this->sources)) {
            $citationsHtml .= '<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ded7ca;">';
            $citationsHtml .= '<h4 style="margin: 0 0 10px 0; color: #181b1f; font-size: 13px; font-weight: 700;">Document References:</h4>';
            foreach ($this->sources as $source) {
                $citationsHtml .= '<span style="display: inline-block; background-color: #f2f4ef; border: 1px solid #d7ded1; border-radius: 4px; padding: 4px 8px; margin: 0 8px 8px 0; font-size: 11px; font-weight: 600; color: #d22630;">';
                $citationsHtml .= htmlspecialchars($source['citation_label']) . ' ' . htmlspecialchars($source['title']);
                $citationsHtml .= '</span>';
            }
            $citationsHtml .= '</div>';
        }

        $noticeHeader = '';
        if ($this->isEscalated) {
            $noticeHeader = '<div style="background-color: #fff5f5; border: 1px solid #fed7d7; border-left: 4px solid #f56565; color: #c53030; padding: 12px; border-radius: 6px; font-size: 13px; font-weight: 600; margin-bottom: 20px;">';
            $noticeHeader .= '⚠️ Escalation Registered: I could not verify a confirmed answer in our company policies. Your query has been escalated to our HR helpdesk. An owner will contact you shortly.';
            $noticeHeader .= '</div>';
        }

        $formattedText = nl2br(htmlspecialchars($this->bodyText));

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$this->replySubject}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #faf8f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #faf8f5; padding: 40px 20px;">
    <tr>
      <td align="center">
        <table width="100%" max-width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border: 1px solid #ded7ca; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
          <!-- Header -->
          <tr>
            <td style="background-color: #d22630; padding: 24px 30px; text-align: left;">
              <span style="color: #ffffff; font-size: 20px; font-weight: 800; letter-spacing: -0.02em;">Aprilo AI Support</span>
            </td>
          </tr>
          <!-- Body -->
          <tr>
            <td style="padding: 35px 30px; text-align: left; line-height: 1.6; color: #4c5551; font-size: 14px;">
              {$noticeHeader}
              <div style="color: #181b1f; font-size: 15px; margin-bottom: 24px;">
                {$formattedText}
              </div>
              {$citationsHtml}
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td style="background-color: #fdfcf9; border-top: 1px solid #ded7ca; padding: 20px 30px; text-align: center; font-size: 11px; color: #888;">
              Sent by **{$this->senderName}** • Powered by Aprilo Operations Agent
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
