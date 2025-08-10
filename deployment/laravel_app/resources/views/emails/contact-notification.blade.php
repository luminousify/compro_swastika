<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">
        New Contact Message
    </h1>
    
    <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2 style="color: #1f2937; margin-top: 0;">Message Details</h2>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; width: 120px;">From:</td>
                <td style="padding: 8px 0;">{{ $contactMessage->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                <td style="padding: 8px 0;">
                    <a href="mailto:{{ $contactMessage->email }}" style="color: #2563eb; text-decoration: none;">
                        {{ $contactMessage->email }}
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Subject:</td>
                <td style="padding: 8px 0;">{{ $contactMessage->subject }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">IP Address:</td>
                <td style="padding: 8px 0;">{{ $contactMessage->created_by_ip }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Received:</td>
                <td style="padding: 8px 0;">{{ $contactMessage->created_at->format('d M Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>
    
    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 20px 0;">
        <h3 style="color: #1f2937; margin-top: 0;">Message:</h3>
        <div style="white-space: pre-wrap; background-color: #f9fafb; padding: 15px; border-radius: 4px; border-left: 4px solid #2563eb;">
            {{ $contactMessage->message }}
        </div>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
        <p>This message was sent via the contact form on your website.</p>
        <p>You can respond directly by replying to this email.</p>
    </div>
</body>
</html>