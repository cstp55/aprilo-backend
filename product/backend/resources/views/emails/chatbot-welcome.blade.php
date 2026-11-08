<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Aprilo Chatbot</title>
</head>
<body style="margin: 0; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; line-height: 1.6;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <!-- Header -->
        <tr>
            <td style="padding: 32px 32px 24px; text-align: center; background-color: #ffffff; border-bottom: 1px solid #f1f5f9;">
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 22px; font-weight: 700; color: #0f172a;">
                    <span style="color: #2563eb;">✦</span> Aprilo Chatbot
                </div>
                <h1 style="margin: 16px 0 6px; font-size: 22px; font-weight: 700; color: #0f172a;">Welcome to Aprilo, {{ $userName }}!</h1>
                <p style="margin: 0; font-size: 14px; color: #64748b;">Your 30-day all-inclusive free trial for <strong>{{ $orgName }}</strong> is now active.</p>
            </td>
        </tr>

        <!-- Main Body -->
        <tr>
            <td style="padding: 32px;">
                <p style="margin-top: 0; font-size: 15px; color: #334155;">
                    With our hybrid AI + human agent chatbot, you can now automate routine customer inquiries, capture qualified leads 24/7, and seamlessly redirect urgent queries to WhatsApp.
                </p>

                <!-- 3 Quick Steps -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin: 24px 0;">
                    <h2 style="margin: 0 0 14px; font-size: 15px; font-weight: 700; color: #0f172a;">3 Quick Wins to Complete Today:</h2>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="28" valign="top" style="font-weight: 700; color: #2563eb;">1.</td>
                            <td style="font-size: 14px; color: #334155; padding-bottom: 10px;">
                                <strong>Name Your Assistant:</strong> Choose a persona and brand color that matches {{ $orgName }}.
                            </td>
                        </tr>
                        <tr>
                            <td width="28" valign="top" style="font-weight: 700; color: #2563eb;">2.</td>
                            <td style="font-size: 14px; color: #334155; padding-bottom: 10px;">
                                <strong>Upload Knowledge Base:</strong> Upload your FAQs, catalog documents, or crawl your website.
                            </td>
                        </tr>
                        <tr>
                            <td width="28" valign="top" style="font-weight: 700; color: #2563eb;">3.</td>
                            <td style="font-size: 14px; color: #334155;">
                                <strong>Embed the Widget:</strong> Add our 1-line script snippet right before the <code>&lt;/body&gt;</code> tag.
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Your Embed Key Box -->
                <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1e40af; margin-bottom: 4px;">Your Public Widget Key</div>
                    <code style="font-family: monospace; font-size: 14px; color: #1d4ed8; font-weight: 600;">{{ $widgetKey }}</code>
                </div>

                <!-- CTA Button -->
                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ $setupUrl }}" style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px; padding: 12px 28px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">Launch Widget Setup Wizard &rarr;</a>
                </div>

                <!-- Support Callout -->
                <p style="margin-bottom: 0; font-size: 13.5px; color: #64748b; line-height: 1.5;">
                    Need assistance setting up? Reply directly to this email or connect with our product engineering team directly on WhatsApp:
                    <a href="{{ $supportWhatsApp }}" style="color: #2563eb; font-weight: 600; text-decoration: none;">+91 91940 05010</a>.
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;">
                <p style="margin: 0 0 4px;">Aprilo Infotech • Next-Generation Autonomous Customer Engagement</p>
                <p style="margin: 0;">© 2026 Aprilo Infotech. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
