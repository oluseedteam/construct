<?php

namespace App\Models;

use App\Support\Pages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key', 'title', 'slug', 'nav_label', 'subtitle', 'content', 'meta_description',
    'is_builtin', 'is_published', 'show_in_nav', 'show_in_footer', 'sort_order',
])]
class Page extends Model
{
    /** Built-in pages in default navigation order: key => [title, slug, nav label, route name]. */
    public const BUILTIN = [
        'home' => ['Home', '', 'Home', 'home'],
        'about' => ['About Us', 'about', 'About', 'about'],
        'services' => ['Our Services', 'services', 'Services', 'services.index'],
        'projects' => ['Our Projects', 'projects', 'Projects', 'projects.index'],
        'gallery' => ['Project Gallery', 'gallery', 'Gallery', 'gallery'],
        'why-us' => ['Why Choose Us', 'why-choose-us', 'Why Choose Us', 'why-us'],
        'careers' => ['Careers', 'careers', 'Careers', 'careers.index'],
        'contact' => ['Contact Us', 'contact', 'Contact', 'contact'],
        'quote' => ['Request a Quote', 'request-a-quote', 'Get a Quote', 'quote'],
    ];

    /** The homepage can never be hidden. */
    public const ALWAYS_PUBLISHED = ['home'];

    protected function casts(): array
    {
        return [
            'is_builtin' => 'boolean',
            'is_published' => 'boolean',
            'show_in_nav' => 'boolean',
            'show_in_footer' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Pages::flush());
        static::deleted(fn () => Pages::flush());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_builtin', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Public URL: the named route for built-ins, /{slug} for custom pages. */
    public function url(): string
    {
        if ($this->is_builtin && isset(self::BUILTIN[$this->key])) {
            return route(self::BUILTIN[$this->key][3]);
        }

        return route('pages.show', $this->slug);
    }

    public function isLocked(): bool
    {
        return $this->is_builtin && in_array($this->key, self::ALWAYS_PUBLISHED, true);
    }

    /**
     * Make sure every built-in page has a row. Idempotent: existing rows (and any admin edits
     * to them) are left alone. Used by the seeder and by Admin > Pages, so a database that was
     * imported from an older SQL file still gets its pages list.
     */
    /** Self-heal: a database imported from an older SQL export has no page rows yet. */
    public static function ensureBuiltinsIfMissing(): void
    {
        if (static::where('is_builtin', true)->count() < count(self::BUILTIN)) {
            static::ensureBuiltins();
        }
    }

    public static function ensureBuiltins(): void
    {
        foreach (array_keys(self::BUILTIN) as $i => $key) {
            [$title, $slug, $navLabel] = self::BUILTIN[$key];

            static::firstOrCreate(['key' => $key], [
                'title' => $title,
                'slug' => $slug === '' ? 'home' : $slug,
                'nav_label' => $navLabel,
                'is_builtin' => true,
                'is_published' => true,
                // "Get a Quote" is the header button, not a menu link.
                'show_in_nav' => $key !== 'quote',
                'show_in_footer' => true,
                'sort_order' => $i * 10,
            ]);
        }
    }
}
