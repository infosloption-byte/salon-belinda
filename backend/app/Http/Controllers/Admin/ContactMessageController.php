<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::latest()->paginate(20);

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function markRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'read']);
        ActivityLogger::log('contact_message.marked_read', "Marked message from {$contactMessage->name} as read", $contactMessage);

        return back()->with('success', 'Marked as read.');
    }

    public function markReplied(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'replied']);
        ActivityLogger::log('contact_message.marked_replied', "Marked message from {$contactMessage->name} as replied", $contactMessage);

        return back()->with('success', 'Marked as replied.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        ActivityLogger::log('contact_message.deleted', "Deleted message from {$contactMessage->name}", $contactMessage);
        $contactMessage->delete();

        return back()->with('success', 'Message deleted.');
    }
}
