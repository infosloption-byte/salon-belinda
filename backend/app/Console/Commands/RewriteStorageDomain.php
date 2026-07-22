<?php

namespace App\Console\Commands;

use App\Models\AlbumPhoto;
use App\Models\GalleryItem;
use App\Models\Product;
use Illuminate\Console\Command;

/**
 * One-time fixup for the belinda.ycusriya.online -> salon.ycusriya.online
 * domain migration. Storage::disk('public')->url() bakes the *absolute*
 * domain (from APP_URL at upload time) into gallery_items.image,
 * album_photos.image, and products.images — so changing APP_URL alone
 * leaves every already-uploaded image pointing at the old domain.
 *
 * Usage: php artisan storage:rewrite-domain "https://belinda.ycusriya.online" "https://api.salon.ycusriya.online" [--dry-run]
 */
class RewriteStorageDomain extends Command
{
    protected $signature = 'storage:rewrite-domain {from} {to} {--dry-run}';
    protected $description = 'Rewrite absolute storage URLs from an old domain to a new one across gallery_items, album_photos, and products.';

    public function handle(): int
    {
        $from = rtrim($this->argument('from'), '/');
        $to = rtrim($this->argument('to'), '/');
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Rewriting {$from} -> {$to}");

        $galleryCount = 0;
        GalleryItem::where('image', 'like', "{$from}%")->each(function (GalleryItem $item) use ($from, $to, $dryRun, &$galleryCount) {
            $galleryCount++;
            $new = str_replace($from, $to, $item->image);
            $this->line("  gallery_items#{$item->id}: {$item->image} -> {$new}");
            if (! $dryRun) {
                $item->update(['image' => $new]);
            }
        });

        $albumCount = 0;
        AlbumPhoto::where('image', 'like', "{$from}%")->each(function (AlbumPhoto $photo) use ($from, $to, $dryRun, &$albumCount) {
            $albumCount++;
            $new = str_replace($from, $to, $photo->image);
            $this->line("  album_photos#{$photo->id}: {$photo->image} -> {$new}");
            if (! $dryRun) {
                $photo->update(['image' => $new]);
            }
        });

        $productCount = 0;
        Product::whereNotNull('images')->each(function (Product $product) use ($from, $to, $dryRun, &$productCount) {
            $images = $product->images ?? [];
            $needsUpdate = false;
            $new = array_map(function ($url) use ($from, $to, &$needsUpdate) {
                if (is_string($url) && str_starts_with($url, $from)) {
                    $needsUpdate = true;
                    return str_replace($from, $to, $url);
                }
                return $url;
            }, $images);

            if ($needsUpdate) {
                $productCount++;
                $this->line("  products#{$product->id}: ".json_encode($images).' -> '.json_encode($new));
                if (! $dryRun) {
                    $product->update(['images' => $new]);
                }
            }
        });

        $this->info("Done. gallery_items: {$galleryCount}, album_photos: {$albumCount}, products: {$productCount}".($dryRun ? ' (dry run — nothing written)' : ''));

        return self::SUCCESS;
    }
}
