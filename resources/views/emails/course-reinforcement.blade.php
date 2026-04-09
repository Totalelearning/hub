<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Check: {{ $courseTitle }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.06);overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#3b82f6,#6366f1);padding:32px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:600;">Knowledge Check</h1>
                            <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">{{ $courseTitle }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 40px;">
                            <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">Hi {{ $learnerName }},</p>
                            <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">You completed <strong>{{ $courseTitle }}</strong> {{ $delayDays }} days ago. To make sure the knowledge has stuck, we have a short set of questions for you.</p>
                            <p style="margin:0 0 24px;color:#334155;font-size:15px;line-height:1.6;">If there are any gaps, we'll reassign the relevant module so you can brush up on that topic.</p>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 24px;">
                                        <a href="{{ $actionUrl }}" style="display:inline-block;background:#3b82f6;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-size:15px;font-weight:600;">Start Knowledge Check</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.5;">If the button doesn't work, copy and paste this URL into your browser:</p>
                            <p style="margin:0 0 24px;color:#3b82f6;font-size:13px;line-height:1.5;word-break:break-all;">{{ $actionUrl }}</p>
                            <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">
                            <p style="margin:0;color:#94a3b8;font-size:12px;line-height:1.5;">This is an automated knowledge check from your learning platform. It only takes a few minutes.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
