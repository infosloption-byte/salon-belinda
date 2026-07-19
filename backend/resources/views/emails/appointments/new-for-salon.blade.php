@extends('emails.layout')

@section('title', 'New Appointment')
@section('eyebrow', 'New Appointment')

@section('content')
    <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
        A new appointment request just came in from <strong>{{ $appointment->name }}</strong>.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3ECE9; margin-bottom: 24px;">
        <tr>
            <td style="padding: 18px 20px; font-size: 14px; color:#241A21;">
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $appointment->service_name ?? 'Not specified' }}</p>
                <p style="margin:0 0 8px;"><strong>Date:</strong> {{ $appointment->date->format('l, d M Y') }}</p>
                <p style="margin:0 0 8px;"><strong>Time:</strong> {{ $appointment->time }}</p>
                <p style="margin:0 0 8px;"><strong>Phone:</strong> {{ $appointment->phone }}</p>
                <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $appointment->email ?: '—' }}</p>
                @if ($appointment->notes)
                    <p style="margin:12px 0 0;"><strong>Notes:</strong><br>{{ $appointment->notes }}</p>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:0 0 24px; font-size: 14px; color:#241A21; opacity:0.7;">
        Call {{ $appointment->phone }} to confirm availability, then update the status from the dashboard.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background-color:#C23056; border-radius: 2px;">
                <a href="{{ route('admin.appointments.index') }}" style="display:inline-block; padding: 12px 24px; font-size: 13px; letter-spacing: 1px; text-transform: uppercase; color:#FBF7F3; text-decoration:none;">
                    View in Dashboard
                </a>
            </td>
        </tr>
    </table>
@endsection
