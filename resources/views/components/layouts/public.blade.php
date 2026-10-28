@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'transparentNav' => false,
])
@php
    $company = setting('company_name');
    $pageTitle = $title ? $title.' | '.$company : setting('seo_title');
    $metaDescription = $description ?: setting('seo_description');
    $ogImage = $image ?: (setting('seo_image') ? media_url(setting('seo_image')) : asset('images/og-image.jpg'));
    $navServices = \Illuminate\Support\Facades\Cache::remember(\App\Models\Service::NAV_CACHE_KEY, 600, fn () => \App\Models\Service::published()->ordered()->get(['name', 'slug'])->map(fn ($s) => ['name' => $s->name, 'slug' => $s->slug])->all());
    $socials = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'x_twitter' => 'X', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'])
        ->filter(fn ($label, $key) => setting($key));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="oJRZCJwTgFHG26tPNM2Rmtx2sAIDlygBjQ42O2EqcD0" />
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($metaDescription), 160) }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($noindex)<meta name="robots" content="noindex, nofollow">@endif
    <meta name="theme-color" content="#082C50">

    <meta property="og:site_name" content="{{ $company }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($metaDescription), 200) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">

    <x-favicons />
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'GeneralContractor',
            'name' => $company,
            'url' => url('/'),
            'logo' => setting('logo') ? media_url(setting('logo')) : asset('favicon-192.png'),
            'image' => asset('images/og-image.jpg'),
            'email' => setting('email'),
            'telephone' => setting('phone'),
            'address' => setting('address') ? ['@type' => 'PostalAddress', 'streetAddress' => setting('address')] : null,
            'sameAs' => $socials->keys()->map(fn ($k) => setting($k))->values()->all() ?: null,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
    {{ $head ?? '' }}
</head>
<body class="min-h-screen">
<a href="#main" class="sr-only z-[100] rounded-lg bg-brand px-4 py-2 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

{{-- ============ Header ============ --}}
<header x-data="{ open: false, scrolled: window.scrollY > 8 }" @scroll.window.passive="scrolled = window.scrollY > 8"
        class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
        :class="scrolled || open ? 'bg-white/90 shadow-[0_8px_30px_-12px_rgba(8,44,80,0.25)] backdrop-blur-xl' : '{{ $transparentNav ? 'bg-transparent' : 'bg-white/90 backdrop-blur-xl' }}'">
    <div class="container-x flex h-[4.5rem] items-center justify-between gap-6 lg:h-20">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ $company }} home"><x-logo /></a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
            @php
                $linkClass = fn ($active) => 'relative rounded-lg px-4 py-2 text-sm font-semibold transition hover:text-brand '.($active ? 'text-brand' : 'text-navy');
                $underline = fn ($active) => $active ? '<span class="absolute inset-x-4 -bottom-0.5 h-0.5 rounded bg-brand"></span>' : '';
            @endphp
            @php
                // Navigation is built from Admin > Pages: only published pages marked "show in menu".
                $navPages = \App\Support\Pages::nav();
                $navHas = fn ($key) => $navPages->contains('key', $key);
                $activeFor = function (array $row) {
                    return match ($row['key']) {
                        'home' => request()->routeIs('home'),
                        'about' => request()->routeIs('about'),
                        'services' => request()->routeIs('services.*'),
                        'projects' => request()->routeIs('projects.*') || request()->routeIs('gallery'),
                        'gallery' => request()->routeIs('gallery'),
                        'why-us' => request()->routeIs('why-us'),
                        'careers' => request()->routeIs('careers.*'),
                        'contact' => request()->routeIs('contact'),
                        'quote' => request()->routeIs('quote'),
                        default => request()->routeIs('pages.show') && request()->route('slug') === $row['slug'],
                    };
                };
                $dropdownItem = 'block rounded-xl px-4 py-2.5 text-sm font-medium text-navy transition hover:bg-brand-50 hover:text-brand';
            @endphp
            @foreach ($navPages as $row)
                @php $active = $activeFor($row); @endphp
                @if ($row['key'] === 'services')
                    {{-- Services dropdown --}}
                    <div class="relative" x-data="{ dd: false }" @mouseenter="dd = true" @mouseleave="dd = false" @keydown.escape.window="dd = false">
                        <a href="{{ $row['url'] }}" class="{{ $linkClass($active) }} inline-flex items-center gap-1" @focus="dd = true" aria-haspopup="true" :aria-expanded="dd">
                            {{ $row['nav_label'] }} <x-icon name="chevron-down" class="size-3.5 transition" ::class="dd && 'rotate-180'" />
                            {!! $underline($active) !!}
                        </a>
                        @if (count($navServices))
                            <div x-show="dd" x-cloak x-transition.opacity.scale.origin.top.duration.200ms class="absolute left-0 top-full w-72 pt-3">
                                <ul class="overflow-hidden rounded-2xl bg-white p-2 shadow-lift ring-1 ring-line">
                                    @foreach ($navServices as $svc)
                                        <li><a href="{{ route('services.show', $svc['slug']) }}" class="{{ $dropdownItem }}">{{ $svc['name'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @elseif ($row['key'] === 'projects')
                    {{-- Projects dropdown (Gallery nests under it while both are published) --}}
                    <div class="relative" x-data="{ dd: false }" @mouseenter="dd = true" @mouseleave="dd = false" @keydown.escape.window="dd = false">
                        <a href="{{ $row['url'] }}" class="{{ $linkClass($active) }} inline-flex items-center gap-1" @focus="dd = true" aria-haspopup="true" :aria-expanded="dd">
                            {{ $row['nav_label'] }} <x-icon name="chevron-down" class="size-3.5 transition" ::class="dd && 'rotate-180'" />
                            {!! $underline($active) !!}
                        </a>
                        <div x-show="dd" x-cloak x-transition.opacity.scale.origin.top.duration.200ms class="absolute left-0 top-full w-56 pt-3">
                            <ul class="overflow-hidden rounded-2xl bg-white p-2 shadow-lift ring-1 ring-line">
                                <li><a href="{{ $row['url'] }}" class="{{ $dropdownItem }}">Project Portfolio</a></li>
                                @if ($navHas('gallery'))
                                    <li><a href="{{ $navPages->firstWhere('key', 'gallery')['url'] }}" class="{{ $dropdownItem }}">{{ $navPages->firstWhere('key', 'gallery')['nav_label'] }}</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @elseif ($row['key'] === 'gallery' && $navHas('projects'))
                    {{-- shown inside the Projects dropdown --}}
                @elseif ($row['key'] === 'why-us')
                    <a href="{{ $row['url'] }}" class="{{ $linkClass($active) }}"><span class="xl:hidden">Why Us</span><span class="hidden xl:inline">{{ $row['nav_label'] }}</span> {!! $underline($active) !!}</a>
                @else
                    <a href="{{ $row['url'] }}" class="{{ $linkClass($active) }}">{{ $row['nav_label'] }} {!! $underline($active) !!}</a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            @if ($ctaUrl = page_url(['quote', 'contact']))
                <a href="{{ $ctaUrl }}" class="btn btn-primary hidden sm:inline-flex">{{ page_published('quote') ? 'Get a Quote' : 'Contact Us' }} <x-icon name="arrow-right" class="size-4" /></a>
            @endif
            <button type="button" class="inline-flex size-11 items-center justify-center rounded-xl text-navy transition hover:bg-navy/5 lg:hidden"
                    @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Toggle menu">
                <span x-show="!open"><x-icon name="menu" class="size-6" /></span>
                <span x-show="open" x-cloak><x-icon name="x" class="size-6" /></span>
            </button>
        </div>
    </div>

    {{-- Mobile navigation --}}
    <div id="mobile-nav" x-show="open" x-cloak x-collapse class="border-t border-line bg-white lg:hidden">
        <nav class="container-x flex flex-col gap-1 py-4" aria-label="Mobile">
            @foreach (\App\Support\Pages::nav() as $row)
                <a href="{{ $row['url'] }}" class="rounded-xl px-4 py-3 text-base font-semibold text-navy transition hover:bg-brand-50 hover:text-brand">{{ $row['nav_label'] }}</a>
            @endforeach
            @if ($ctaUrl = page_url(['quote', 'contact']))
                <a href="{{ $ctaUrl }}" class="btn btn-primary mt-2">{{ page_published('quote') ? 'Get a Quote' : 'Contact Us' }} <x-icon name="arrow-right" class="size-4" /></a>
            @endif
        </nav>
    </div>
</header>

<main id="main">
    {{ $slot }}
</main>

{{-- ============ Footer ============ --}}
<footer class="bg-navy-900 text-white/75">
    <div class="container-x grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <a href="{{ route('home') }}" aria-label="{{ $company }} home"><x-logo light tagline /></a>
            <p class="mt-5 max-w-xs text-sm leading-relaxed">{{ content('footer.about') }}</p>
            @if ($socials->isNotEmpty())
                <ul class="mt-6 flex gap-3">
                    @foreach ($socials as $key => $label)
                        <li><a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $company }} on {{ $label }}"
                               class="inline-flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:-translate-y-0.5 hover:bg-brand"><x-icon :name="$key" class="size-4.5" /></a></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h2 class="font-display text-sm font-bold uppercase tracking-widest !text-white">Quick Links</h2>
            <ul class="mt-5 space-y-3 text-sm">
                @foreach (\App\Support\Pages::footer() as $row)
                    <li><a href="{{ $row['url'] }}" class="transition hover:text-brand">{{ $row['title'] }}</a></li>
                @endforeach
            </ul>
        </div>

        @if (page_published('services'))
        <div>
            <h2 class="font-display text-sm font-bold uppercase tracking-widest !text-white">Services</h2>
            <ul class="mt-5 space-y-3 text-sm">
                @forelse ($navServices as $svc)
                    <li><a href="{{ route('services.show', $svc['slug']) }}" class="transition hover:text-brand">{{ $svc['name'] }}</a></li>
                @empty
                    <li><a href="{{ route('services.index') }}" class="transition hover:text-brand">Our Services</a></li>
                @endforelse
            </ul>
        </div>
        @endif

        <div>
            <h2 class="font-display text-sm font-bold uppercase tracking-widest !text-white">Contact</h2>
            <ul class="mt-5 space-y-4 text-sm">
                @if (setting('address'))
                    <li class="flex gap-3"><x-icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-brand" /><span>{{ setting('address') }}</span></li>
                @endif
                @if (setting('phone'))
                    <li class="flex gap-3"><x-icon name="phone" class="mt-0.5 size-5 shrink-0 text-brand" /><a href="{{ tel_href(setting('phone')) }}" class="hover:text-brand">{{ setting('phone') }}</a></li>
                @endif
                @if (setting('email'))
                    <li class="flex gap-3"><x-icon name="mail" class="mt-0.5 size-5 shrink-0 text-brand" /><a href="mailto:{{ setting('email') }}" class="break-all hover:text-brand">{{ setting('email') }}</a></li>
                @endif
                @if (! setting('address') && ! setting('phone') && ! setting('email') && page_published('contact'))
                    <li><a href="{{ route('contact') }}" class="hover:text-brand">Send us a message</a></li>
                @endif
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="container-x flex flex-col items-center justify-between gap-2 py-6 text-xs sm:flex-row">
            <p>{{ content('footer.copyright') ?: '© '.date('Y').' '.$company.'. All rights reserved.' }}</p>
            <p class="text-white/50">{{ setting('tagline') }}</p>
        </div>
    </div>
</footer>

@if ($wa = whatsapp_href(setting('whatsapp')))
    <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp"
       class="fixed bottom-5 right-5 z-40 inline-flex size-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/25 transition hover:scale-110">
        <x-icon name="whatsapp" class="size-7" />
    </a>
@endif

@livewireScriptConfig
</body>
</html>
