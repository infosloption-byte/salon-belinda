<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewContactMessageNotification;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = ContactMessage::create($data + ['status' => 'new']);

        try {
            Mail::to(config('notifications.salon_email'))->send(new NewContactMessageNotification($message));
        } catch (\Throwable $e) {
            Log::error('Failed to send new contact message notification', ['contact_message_id' => $message->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'message' => "Thank you — we've received your message and will reply soon.",
            'contact_message' => $message,
        ], 201);
    }
}
