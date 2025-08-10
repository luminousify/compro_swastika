<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank you for contacting {{ $companyName }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">
        Thank you for contacting {{ $companyName }}
    </h1>
    
    <div style="background-color: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2563eb;">
        <p style="margin: 0; font-size: 16px;">
            Dear <strong>{{ $contactMessage->name }}</strong>,
        </p>
        <p style="margin: 10px 0 0 0;">
            We have received your message and will get back to you as soon as possible, usually within 24 hours during business days.
        </p>
    </div>
    
    <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Your Message Summary:</h2>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; width: 80px;">Subject:</td>
                <td style="padding: 8px 0;">{{ $contactMessage->subject }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Sent:</td>
                <td style="padding: 8px 0;">{{ $contactMessage->created_at->format('d M Y, H:i') }} WIB</td>
            </tr>
        </table>
        
        <div style="margin-top: 15px;">
            <strong>Your Message:</strong>
            <div style="background-color: #ffffff; padding: 15px; border-radius: 4px; margin-top: 5px; white-space: pre-wrap; border: 1px solid #e5e7eb;">
                {{ $contactMessage->message }}
            </div>
        </div>
    </div>
    
    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 20px 0;">
        <h3 style="color: #1f2937; margin-top: 0;">What happens next?</h3>
        <ul style="margin: 0; padding-left: 20px;">
            <li>Our team will review your message</li>
            <li>We will respond within 24 hours during business days</li>
            <li>You will receive a reply at <strong>{{ $contactMessage->email }}</strong></li>
        </ul>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; text-align: center;">
        <p>Thank you for your interest in {{ $companyName }}.</p>
        <p style="margin: 5px 0;">This is an automated response. Please do not reply to this email.</p>
    </div>
</body>
</html>