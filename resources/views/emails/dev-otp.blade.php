<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Developer Access OTP</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #1E4658; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 20px; }
        h1 { color: #1E4658; font-size: 24px; margin: 0; }
        .content { font-size: 16px; line-height: 1.6; color: #374151; text-align: center; }
        .otp-box { display: inline-block; background-color: #1E4658; color: #ffffff; font-size: 32px; font-weight: bold; letter-spacing: 5px; padding: 15px 30px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; font-size: 12px; color: #5C6F77; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PropManager Developer Access</h1>
        </div>
        <div class="content">
            <p>You have requested a secure developer login key.</p>
            <p>Your One-Time Password (OTP) is:</p>
            <div class="otp-box">{{ $otp }}</div>
            <p>This code will expire in <strong>5 minutes</strong>.</p>
            <p>If you did not request this, please ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PropManager. All rights reserved.
        </div>
    </div>
</body>
</html>
