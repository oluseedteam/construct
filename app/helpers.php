<?php

use App\Support\Site;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /** A site setting: admin-saved value if present, else the configured default. */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(Site::class)->setting($key, $default);
    }
}

if (! function_exists('content')) {
    /** Editable page copy: admin-saved value if present, else the configured default. */
    function content(string $key, mixed $default = null): mixed
    {
        return app(Site::class)->content($key, $default);
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL for a stored path. Paths beginning with "images/" are bundled
     * with the app (public/images); everything else lives on the public disk.
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback ? asset($fallback) : null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('paragraphs')) {
    /** Split admin-entered plain text into paragraphs (blank-line separated). */
    function paragraphs(?string $text): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R{2,}/', (string) $text)),
            fn ($p) => $p !== ''
        ));
    }
}

if (! function_exists('tel_href')) {
    /** Strip a phone number down to a tel: link. */
    function tel_href(?string $phone): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', (string) $phone);
    }
}

if (! function_exists('whatsapp_href')) {
    function whatsapp_href(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        return $digits ? 'https://wa.me/'.$digits : null;
    }
}
