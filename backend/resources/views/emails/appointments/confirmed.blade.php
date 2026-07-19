@extends('emails.layout')

@section('title', 'Appointment Confirmed')
@section('eyebrow', 'Appointment Confirmed')

@section('content')
    <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
        Hi {{ $appointment->name }}, your appointment is confirmed. We're looking forward to seeing you.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3ECE9; margin-bottom: 24px;">
        <tr>
            <td style="padding: 18px 20px; font-size: 14px; color:#241A21;">
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $appointment->service_name ?? 'Not specified' }}</p>
                <p style="margin:0 0 8px;"><strong>Date:</strong> {{ $appointment->date->format('l, d M Y') }}</p>
                <p style="margin:0;"><strong>Time:</strong> {{ $appointment->time }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size: 14px; color:#241A21; opacity:0.7;">
        Need to change anything? Just call or WhatsApp us at {{ $sitePhone ?? '070 244 4393' }}.
    </p>
@endsection
