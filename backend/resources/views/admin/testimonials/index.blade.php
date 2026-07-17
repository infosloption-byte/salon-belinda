@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('admin.testimonials.index', array_filter(['status' => $value])) }}"
               class="btn {{ request('status', '') === $value ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($testimonials as $t)
            <div class="bg-white p-5">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="font-medium">{{ $t->name }}</p>
                        <p class="text-xs opacity-60">{{ $t->service }} · {{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</p>
                    </div>
                    <span class="badge" style="background:#F3ECE3;">{{ $t->status }}</span>
                </div>
                <p class="text-sm opacity-75 mb-4">{{ $t->message }}</p>
                <div class="flex gap-2">
                    @if ($t->status !== 'approved')
                        <form method="POST" action="{{ route('admin.testimonials.status', $t) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button class="btn btn-primary">Approve</button>
                        </form>
                    @endif
                    @if ($t->status !== 'rejected')
                        <form method="POST" action="{{ route('admin.testimonials.status', $t) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button class="btn btn-outline">Reject</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('Delete this review?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline" style="color:#7A2E3A;">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm opacity-60 py-8 text-center bg-white">No reviews found.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $testimonials->links() }}</div>
@endsection
