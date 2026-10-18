@props(['title' => 'Dashboard'])
@php
    $user = auth()->user();
    $newMessages = \App\Models\ContactMessage::where('status', 'new')->count();
    $pendingQuotes = \App\Models\QuoteRequest::where('status', 'new')->count();
    $newApplications = \App\Models\JobApplication::where('status', 'new')->count();
    // [label, route, icon, active pattern, badge, section heading shown above this item]
    $nav = [
        ['Dashboard', 'admin.dashboard', 'dashboard', 'admin.dashboard', null, null],
        ['Quote Requests', 'admin.quotes.index', 'document', 'admin.quotes.*', $pendingQuotes, 'Inbox'],
        ['Messages', 'admin.messages.index', 'mail', 'admin.messages.*', $newMessages, null],
        ['Job Applications', 'admin.applications.index', 'users', 'admin.applications.*', $newApplications, null],
        ['Projects', 'admin.projects.index', 'building', 'admin.projects.*', null, 'Content'],
        ['Services', 'admin.services.index', 'tools', 'admin.services.*', null, null],
        ['Gallery', 'admin.gallery.index', 'photo', 'admin.gallery.*', null, null],
        ['Job Openings', 'admin.jobs.index', 'handshake', 'admin.jobs.*', null, null],
        ['Testimonials', 'admin.testimonials.index', 'quote', 'admin.testimonials.*', null, null],
        ['Highlights', 'admin.highlights.index', 'check-circle', 'admin.highlights.*', null, null],
        ['Statistics', 'admin.stats.edit', 'hardhat', 'admin.stats.*', null, null],
        ['Website Content', 'admin.content.edit', 'pencil', 'admin.content.*', null, null],
    ];
    if ($user->isAdmin()) {
        $nav[] = ['Settings', 'admin.settings.edit', 'cog', 'admin.settings.*', null, 'Administration'];
        $nav[] = ['Staff Accounts', 'admin.users.index', 'shield', 'admin.users.*', null, null];
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | Admin · {{ setting('company_name') }}</title>
    <x-favicons />
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-mist">
<div x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">
    {{-- Sidebar --}}
    <div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-navy-900/60 lg:hidden" @click="sidebar = false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-navy-900 transition-transform duration-300 lg:translate-x-0"
           :class="sidebar && '!translate-x-0'" aria-label="Admin navigation">
        <div class="flex h-20 items-center justify-between border-b border-white/10 px-6">
            <a href="{{ route('admin.dashboard') }}"><x-logo light size="sm" /></a>
            <button type="button" class="text-white/70 lg:hidden" @click="sidebar = false" aria-label="Close menu"><x-icon name="x" class="size-6" /></button>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
            @foreach ($nav as [$label, $route, $icon, $pattern, $badge, $heading])
                @if ($heading)<p class="px-3 pb-1 pt-5 text-[0.65rem] font-bold uppercase tracking-[0.2em] text-white/40">{{ $heading }}</p>@endif
                <a href="{{ route($route) }}" @class(['admin-link', 'is-active' => request()->routeIs($pattern)])>
                    <x-icon :name="$icon" class="size-5" />
                    <span class="flex-1">{{ $label }}</span>
                    @if ($badge)<span class="rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-white ring-2 ring-navy-900">{{ $badge }}</span>@endif
                </a>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4">
            <a href="{{ route('home') }}" target="_blank" class="admin-link"><x-icon name="external" class="size-5" /> View website</a>
        </div>
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-line bg-white/90 px-4 backdrop-blur sm:px-6 lg:h-20 lg:px-10">
            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex size-10 items-center justify-center rounded-xl text-navy hover:bg-navy/5 lg:hidden" @click="sidebar = true" aria-label="Open menu"><x-icon name="menu" class="size-6" /></button>
                <h1 class="font-display text-lg font-extrabold sm:text-xl">{{ $title }}</h1>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.profile.edit') }}" class="hidden text-right transition hover:opacity-75 sm:block" title="My profile">
                    <span class="block text-sm font-semibold text-navy">{{ $user->name }}</span>
                    <span class="block text-xs capitalize text-slate-500">{{ $user->role }}</span>
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm"><x-icon name="logout" class="size-4" /> Sign out</button>
                </form>
            </div>
        </header>

        <main class="min-w-0 p-4 sm:p-6 lg:p-10">
            <x-flash />
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                    <p class="font-semibold">Please fix the highlighted problems below.</p>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
@livewireScriptConfig
</body>
</html>
