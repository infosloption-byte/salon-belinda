<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0; padding:0; background-color:#F3ECE9; font-family: Helvetica, Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3ECE9; padding: 32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#FBF7F3;">
                <tr>
                    <td style="background-color:#331A2C; padding: 28px 32px;">
                        <p style="margin:0; font-family: Georgia, serif; font-style: italic; font-size: 22px; color:#FBF7F3;">
                            {{ config('app.name') }}
                        </p>
                        <p style="margin:4px 0 0; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color:#F5A623;">
                            @yield('eyebrow', 'Bridal & Ladies Salon')
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 32px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 32px; border-top: 1px solid rgba(36,26,33,0.1);">
                        <p style="margin:0; font-size: 12px; color:#241A21; opacity:0.6;">
                            {{ $siteAddress ?? config('notifications.salon_address') }}<br>
                            {{ $sitePhone ?? config('notifications.salon_phone') }} &middot; {{ $siteEmail ?? config('notifications.salon_email') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
