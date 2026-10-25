<?php

use App\Support\Pages;
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

if (! function_exists('page_published')) {
    /** Whether a built-in page (home, about, services, projects, gallery, why-us, careers, contact, quote) is visible. */
    function page_published(string $key): bool
    {
        return Pages::isPublished($key);
    }
}

if (! function_exists('page_url')) {
    /** URL of the first published page among the given keys (e.g. ['quote', 'contact']), or null if none is. */
    function page_url(array $keys): ?string
    {
        return Pages::firstUrl($keys);
    }
}

if (! function_exists('render_content')) {
    /**
     * Turn admin-entered plain text into safe HTML. Blocks are separated by blank lines;
     * a block starting with "## " becomes a heading and a block of "- " lines becomes a list.
     * Everything is escaped, so no raw HTML can be injected.
     */
    function render_content(?string $text): string
    {
        $html = '';

        foreach (paragraphs($text) as $block) {
            if (preg_match('/^#{1,3}\s+(.+)$/s', $block, $m) && ! str_contains($m[1], "\n")) {
                $html .= '<h2>'.e($m[1]).'</h2>';

                continue;
            }

            $lines = preg_split('/\R/', $block);
            if (count($lines) && count(array_filter($lines, fn ($l) => preg_match('/^[-*]\s+/', $l))) === count($lines)) {
                $html .= '<ul>'.implode('', array_map(fn ($l) => '<li>'.e(preg_replace('/^[-*]\s+/', '', $l)).'</li>', $lines)).'</ul>';

                continue;
            }

            $html .= '<p>'.nl2br(e($block)).'</p>';
        }

        return $html;
    }
}
