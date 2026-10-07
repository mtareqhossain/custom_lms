<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background:#F3F5F7; font-family: 'Public Sans', Arial, sans-serif; color:#10222B;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F5F7; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #D5DCE1;">
                    <tr>
                        <td style="background:#0B6E6E; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:700;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            {!! $bodyHtml !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; background:#DCEFEC; font-size:12px; color:#5B6B75;">
                            You're receiving this email because of your account or activity on {{ config('app.name') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
