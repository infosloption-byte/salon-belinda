<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class AlbumController extends Controller
{
    public function __construct(private readonly ImageUploadService $uploads)
    {
    }

    public function index(Request $request): View
    {
        $albums = Album::query()
            ->withCount('photos')
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('couple_names', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('event_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.albums.index', ['albums' => $albums, 'q' => $request->query('q')]);
    }

    public function create(): View
    {
        return view('admin.albums.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'couple_names' => ['nullable', 'string', 'max:150'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_file' => ['nullable', 'image', 'max:8192'],
            'cover_image' => ['nullable', 'url', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'photo_files' => ['nullable', 'array'],
            'photo_files.*' => ['image', 'max:8192'],
            'photo_urls' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('cover_image_file')) {
            $data['cover_image'] = $this->uploads->store($request->file('cover_image_file'), 'albums');
        }
        $photoUrls = $data['photo_urls'] ?? '';
        unset($data['cover_image_file'], $data['photo_files'], $data['photo_urls']);

        $album = Album::create([
            ...$data,
            'slug' => Album::makeUniqueSlug($data['title']),
            'is_published' => $request->boolean('is_published', true),
        ]);

        $this->attachUploadedPhotos($album, $request->file('photo_files', []));
        $this->attachPhotosFromText($album, $photoUrls);

        // If no cover image was set explicitly, use the first uploaded photo.
        if (! $album->cover_image) {
            $first = $album->photos()->first();
            if ($first) {
                $album->update(['cover_image' => $first->image]);
            }
        }

        return redirect()->route('admin.albums.edit', $album)->with('success', 'Album created.');
    }

    public function edit(Album $album): View
    {
        $album->load('photos');

        return view('admin.albums.edit', ['album' => $album]);
    }

    public function update(Request $request, Album $album): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'couple_names' => ['nullable', 'string', 'max:150'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_file' => ['nullable', 'image', 'max:8192'],
            'cover_image' => ['nullable', 'url', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'photo_files' => ['nullable', 'array'],
            'photo_files.*' => ['image', 'max:8192'],
            'photo_urls' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('cover_image_file')) {
            // Replacing an old self-hosted cover image — clean up the orphaned file.
            $this->uploads->delete($album->cover_image);
            $data['cover_image'] = $this->uploads->store($request->file('cover_image_file'), 'albums');
        }
        $photoUrls = $data['photo_urls'] ?? '';
        unset($data['cover_image_file'], $data['photo_files'], $data['photo_urls']);

        $album->update([
            ...$data,
            'is_published' => $request->boolean('is_published', true),
        ]);

        $this->attachUploadedPhotos($album, $request->file('photo_files', []));
        $this->attachPhotosFromText($album, $photoUrls);

        return back()->with('success', 'Album updated.');
    }

    public function destroy(Album $album): RedirectResponse
    {
        $this->uploads->delete($album->cover_image);
        foreach ($album->photos as $photo) {
            $this->uploads->delete($photo->image);
        }

        $album->delete();

        return redirect()->route('admin.albums.index')->with('success', 'Album deleted.');
    }

    public function destroyPhoto(Album $album, AlbumPhoto $photo): RedirectResponse
    {
        abort_unless($photo->album_id === $album->id, 404);

        $this->uploads->delete($photo->image);
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }

    /**
     * Store uploaded photo files and attach them to the album.
     *
     * @param  array<UploadedFile>  $files
     */
    private function attachUploadedPhotos(Album $album, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $nextOrder = (int) $album->photos()->max('sort_order') + 1;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            AlbumPhoto::create([
                'album_id' => $album->id,
                'image' => $this->uploads->store($file, 'albums'),
                'sort_order' => $nextOrder++,
            ]);
        }
    }

    /**
     * Parse a textarea of one-photo-per-line entries into AlbumPhoto rows.
     * Each line can be a plain image URL, or "URL | Caption".
     */
    private function attachPhotosFromText(Album $album, string $text): void
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        if (empty($lines)) {
            return;
        }

        $nextOrder = (int) $album->photos()->max('sort_order') + 1;

        foreach ($lines as $line) {
            [$url, $caption] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);

            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            AlbumPhoto::create([
                'album_id' => $album->id,
                'image' => $url,
                'caption' => $caption ?: null,
                'sort_order' => $nextOrder++,
            ]);
        }
    }
}
