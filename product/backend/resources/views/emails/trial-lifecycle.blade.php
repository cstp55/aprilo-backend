<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprilo Chatbot Trial Update</title>
</head>
<body style="margin: 0; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; line-height: 1.6;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <!-- Header -->
        <tr>
            <td style="padding: 32px 32px 20px; text-align: center; border-bottom: 1px solid #f1f5f9;">
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; color: #0f172a;">
                    <span style="color: #2563eb;">✦</span> Aprilo Chatbot
                </div>
                <h1 style="margin: 14px 0 4px; font-size: 21px; font-weight: 700; color: #0f172a;">
                    @if ($stage === 'day_3')
                        Early Insights for {{ $orgName }}
                    @elseif ($stage === 'day_10')
                        Supercharge Your Conversions with WhatsApp
                    @elseif ($stage === 'day_27')
                        {{ $daysRemaining }} Days Remaining on Your Trial
                    @else
                        Keep Your Assistant Online
                    @endif
                </h1>
            </td>
        </tr>

        <!-- Main Body -->
        <tr>
            <td style="padding: 32px;">
                <p style="margin-top: 0; font-size: 15px; color: #334155;">Hi {{ $userName }},</p>

                @if ($stage === 'day_3')
                    <p style="font-size: 14.5px; color: #334155;">
                        Your Aprilo assistant has already engaged in <strong>{{ $conversationsCount }} visitor conversations</strong>.
                    </p>
                    <p style="font-size: 14.5px; color: #334155;">
                        Have you reviewed your unanswered questions in the dashboard? Adding 3–5 additional FAQ entries to your knowledge base can increase resolution accuracy by over 40%.
                    </p>
                    <div style="text-align: center; margin: 24px 0;">
                        <a href="{{ $widgetUrl }}" style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 11px 24px; border-radius: 8px; display: inline-block;">Update Knowledge Base &rarr;</a>
                    </div>

                @elseif ($stage === 'day_10')
                    <p style="font-size: 14.5px; color: #334155;">
                        Did you know that over 65% of website visitors prefer reaching out via WhatsApp for urgent medical or purchase inquiries?
                    </p>
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin: 20px 0;">
                        <div style="font-weight: 700; color: #166534; font-size: 14px; margin-bottom: 4px;">WhatsApp Direct Redirect Mode</div>
                        <p style="font-size: 13px; color: #15803d; margin: 0;">
                            Enable "Only WhatsApp Widget" in your settings to immediately route user messages to your team on WhatsApp with the inquiry prefilled.
                        </p>
                    </div>
                    <div style="text-align: center; margin: 24px 0;">
                        <a href="{{ $widgetUrl }}" style="background-color: #16a34a; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 11px 24px; border-radius: 8px; display: inline-block;">Configure WhatsApp Mode &rarr;</a>
                    </div>

                @elseif ($stage === 'day_27')
                    <p style="font-size: 14.5px; color: #334155;">
                        Your 30-day free trial will conclude in <strong>{{ $daysRemaining }} days</strong>. Over the trial, your assistant resolved <strong>{{ $conversationsCount }} inquiries</strong>, automating routine repetitive support.
                    </p>
                    <p style="font-size: 14.5px; color: #334155;">
                        To avoid any disruption to your live website chat, select a plan today to continue uninterrupted.
                    </p>
                    <div style="text-align: center; margin: 24px 0;">
                        <a href="{{ $upgradeUrl }}" style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 11px 24px; border-radius: 8px; display: inline-block;">Lock in Your Plan (₹499/mo) &rarr;</a>
                    </div>

                @else
                    <p style="font-size: 14.5px; color: #334155;">
                        Your 30-day trial has officially concluded. To keep your automated AI assistant active, choose a flexible monthly or annual plan.
                    </p>
                    <p style="font-size: 13.5px; color: #64748b;">
                        <em>Note: Your knowledge-base documents and configurations remain securely stored. Your widget will operate in Free Mode (WhatsApp Connect) until upgraded.</em>
                    </p>
                    <div style="text-align: center; margin: 24px 0;">
                        <a href="{{ $upgradeUrl }}" style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 11px 24px; border-radius: 8px; display: inline-block;">Reactivate Pro Plan &rarr;</a>
                    </div>
                @endif

                <div style="border-top: 1px solid #e2e8f0; margin-top: 24px; padding-top: 16px; font-size: 13px; color: #64748b;">
                    Questions about billing or setup? Contact our team on WhatsApp:
                    <a href="{{ $supportWhatsApp }}" style="color: #2563eb; font-weight: 600; text-decoration: none;">+91 91940 05010</a>.
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 18px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;">
                <p style="margin: 0;">© 2026 Aprilo Infotech • Next-Generation Customer AI</p>
            </td>
        </tr>
    </table>
</body>
</html>
