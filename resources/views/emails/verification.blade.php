<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h2>Hi {{ $user->name }},</h2>
    <p>Thank you for registering! Please verify your email address by using the following verification code in your mobile app:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 3px; margin: 20px 0;">
        {{ $token }}
    </div>
    
    <p>This verification code will expire in 60 minutes.</p>
    
    <p>If you didn't create this account, please ignore this email.</p>
    
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
</body>
</html>
