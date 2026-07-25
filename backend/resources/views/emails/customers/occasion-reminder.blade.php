@extends('emails.layout')

@section('title', $occasion === 'anniversary' ? 'Happy Anniversary' : 'Happy Birthday')
@section('eyebrow', $occasion === 'anniversary' ? 'Happy Anniversary' : 'Happy Birthday')

@section('content')
    @if ($occasion === 'anniversary')
        <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
            Hi {{ $customer->name }}, happy anniversary! We're so glad to have you as part of the
            {{ config('app.name') }} family, and we'd love to help you celebrate.
        </p>
    @else
        <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
            Hi {{ $customer->name }}, happy birthday from all of us at {{ config('app.name') }}! We hope
            your day is as wonderful as you are.
        </p>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3ECE9; margin-bottom: 24px;">
        <tr>
            <td style="padding: 18px 20px; font-size: 14px; color:#241A21;">
                <p style="margin:0;">
                    Treat yourself — book a service this week and let us pamper you.
                    Call or WhatsApp us at {{ $sitePhone ?? config('notifications.salon_phone') }} to grab a slot.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size: 14px; color:#241A21; opacity:0.7;">
        We look forward to seeing you soon.
    </p>
@endsection
