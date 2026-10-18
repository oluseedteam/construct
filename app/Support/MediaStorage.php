<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploaded images on the public disk.
 *
 * Images are decoded and re-encoded with GD, which strips metadata and any
 * payload hidden in the file, and caps the pixel width so huge phone photos
 * do not slow the site down.
 */
class MediaStorage
{
    public const MAX_WIDTH = 2200;

    /** Validation rule fragments shared by all image upload fields. */
    public const IMAGE_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    public static function storeImage(UploadedFile $file, string $directory): string
    {
        $mime = $file->getMimeType();
        $disk = Storage::disk('public');

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };

        if (! $source) {
            // Validation already restricts types; if GD can't decode, store as-is under a random name.
            return $file->store($directory, 'public');
        }

        if (imagesx($source) > self::MAX_WIDTH) {
            $source = imagescale($source, self::MAX_WIDTH);
        }

        [$extension, $encoder] = match ($mime) {
            'image/png' => ['png', fn ($im, $path) => imagepng($im, $path, 7)],
            'image/webp' => ['webp', fn ($im, $path) => imagewebp($im, $path, 82)],
            default => ['jpg', fn ($im, $path) => imagejpeg($im, $path, 82)],
        };

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($source, false);
            imagesavealpha($source, true);
        }

        $relative = trim($directory, '/').'/'.Str::random(32).'.'.$extension;
        $disk->makeDirectory(dirname($relative));

        $encoder($source, $disk->path($relative));

        return $relative;
    }

    /** Delete a stored file, ignoring empty values and assets bundled with the app. */
    public static function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'images/') && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function deleteMany(iterable $paths): void
    {
        foreach ($paths as $path) {
            self::delete($path);
        }
    }
}
