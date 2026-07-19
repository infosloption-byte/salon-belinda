@extends('emails.layout')

@section('title', 'New Message')
@section('eyebrow', 'New Message')

@section('content')
    <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
        A new message came in through the contact form.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3ECE9; margin-bottom: 24px;">
        <tr>
            <td style="padding: 18px 20px; font-size: 14px; color:#241A21;">
                <p style="margin:0 0 8px;"><strong>From:</strong> {{ $contactMessage->name }}</p>
                <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $contactMessage->email }}</p>
                @if ($contactMessage->phone)
                    <p style="margin:0 0 8px;"><strong>Phone:</strong> {{ $contactMessage->phone }}</p>
                @endif
                <p style="margin:12px 0 0;"><strong>Message:</strong><br>{{ $contactMessage->message }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 24px; font-size: 14px; color:#241A21; opacity:0.7;">
        Just hit reply to respond directly to {{ $contactMessage->name }}.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background-color:#C23056; border-radius: 2px;">
                <a href="{{ route('admin.contact-messages.index') }}" style="display:inline-block; padding: 12px 24px; font-size: 13px; letter-spacing: 1px; text-transform: uppercase; color:#FBF7F3; text-decoration:none;">
                    View in Dashboard
                </a>
            </td>
        </tr>
    </table>
@endsection
