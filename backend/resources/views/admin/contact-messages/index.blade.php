@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
    <div class="space-y-4">
        @forelse ($messages as $m)
            <div class="bg-white p-5">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="font-medium">{{ $m->name }}</p>
                        <p class="text-xs opacity-60">{{ $m->email }} @if($m->phone) · {{ $m->phone }} @endif</p>
                    </div>
                    <span class="badge" style="background:#F3ECE3;">{{ $m->status }}</span>
                </div>
                <p class="text-sm opacity-75 mb-4">{{ $m->message }}</p>
                <div class="flex gap-2">
                    @if ($m->status === 'new')
                        <form method="POST" action="{{ route('admin.contact-messages.read', $m) }}">
                            @csrf
                            <button class="btn btn-outline">Mark Read</button>
                        </form>
                    @endif
                    @if ($m->status !== 'replied')
                        <form method="POST" action="{{ route('admin.contact-messages.replied', $m) }}">
                            @csrf
                            <button class="btn btn-primary">Mark Replied</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $m) }}" onsubmit="return confirm('Delete this message?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline" style="color:#7A2E3A;">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm opacity-60 py-8 text-center bg-white">No messages yet.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
