@extends('layouts.admin')

@section('title', 'Wedding Albums')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <form method="GET" class="flex-1 max-w-sm">
            <input
                name="q"
                value="{{ $q }}"
                placeholder="Search by title or couple name..."
                class="w-full border px-3 py-2.5 text-sm"
                style="border-color: rgba(38,34,32,0.2);"
            >
        </form>
        <a href="{{ route('admin.albums.create') }}" class="btn btn-primary">+ New Album</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($albums as $album)
            <a href="{{ route('admin.albums.edit', $album) }}" class="block bg-white hover:shadow-md transition-shadow">
                <div class="aspect-[4/3] overflow-hidden" style="background:#F3ECE3;">
                    @if ($album->cover_image)
                        <img src="{{ $album->cover_image }}" alt="{{ $album->title }}" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-display text-lg">{{ $album->title }}</p>
                        <span class="badge" style="{{ $album->is_published ? 'background:#F3DEDB;color:#7A2E3A;' : 'background:#eee;color:#999;' }}">
                            {{ $album->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="text-xs opacity-60">
                        {{ $album->couple_names }}
                        @if ($album->event_date) &middot; {{ $album->event_date->format('d M Y') }} @endif
                    </p>
                    <p class="text-xs opacity-50 mt-1">{{ $album->photos_count }} photos</p>
                </div>
            </a>
        @empty
            <p class="text-sm opacity-60 col-span-full py-12 text-center bg-white">No albums yet — create your first one.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $albums->links() }}</div>
@endsection
