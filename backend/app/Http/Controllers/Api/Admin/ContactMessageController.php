<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;

/**
 * JSON port of Admin\ContactMessageController — see routes/api.php
 * /api/admin/messages*.
 */
class ContactMessageController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = ContactMessage::latest()->paginate(20);

        return response()->json(['messages' => $messages]);
    }

    public function markRead(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['status' => 'read']);
        ActivityLogger::log('contact_message.marked_read', "Marked message from {$contactMessage->name} as read", $contactMessage);

        return response()->json(['contactMessage' => $contactMessage->fresh(), 'message' => 'Marked as read.']);
    }

    public function markReplied(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['status' => 'replied']);
        ActivityLogger::log('contact_message.marked_replied', "Marked message from {$contactMessage->name} as replied", $contactMessage);

        return response()->json(['contactMessage' => $contactMessage->fresh(), 'message' => 'Marked as replied.']);
    }

    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        ActivityLogger::log('contact_message.deleted', "Deleted message from {$contactMessage->name}", $contactMessage);
        $contactMessage->delete();

        return response()->json(['message' => 'Message deleted.']);
    }
}
