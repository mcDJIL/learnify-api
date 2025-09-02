<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
</head>
<body>
    <h2>Hi {{ $user->name }},</h2>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <p>Please use the following reset code in your mobile app:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 3px; margin: 20px 0;">
        {{ $token }}
    </div>
    
    <p>This password reset code will expire in 60 minutes.</p>
    
    <p>If you did not request a password reset, no further action is required.</p>
    
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
</body>
</html>