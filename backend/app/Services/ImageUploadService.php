<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores admin-uploaded images on the "public" disk and returns the full
 * public URL, so the API/frontend keep working with plain image URL strings
 * exactly like before — nothing downstream needs to know an upload happened.
 */
class ImageUploadService
{
    /**
     * Store an uploaded file under the given folder (e.g. "gallery",
     * "products") and return its full public URL.
     */
    public function store(UploadedFile $file, string $folder): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $filename, 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Delete a previously uploaded image, but only if the URL actually
     * points at our own storage — external/stock URLs are left alone.
     */
    public function delete(?string $url): void
    {
        if (! $url) {
            return;
        }

        $marker = '/storage/';
        $position = strpos($url, $marker);

        if ($position === false) {
            return;
        }

        $relativePath = substr($url, $position + strlen($marker));

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
