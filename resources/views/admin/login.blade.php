<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login | {{ setting('company_name') }}</title>
    <x-favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-navy-900">
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden overflow-hidden lg:block">
        <img src="{{ asset('images/hero-1280.jpg') }}" alt="" class="absolute inset-0 size-full object-cover object-[72%_center]">
        <div class="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy/70 to-navy/40"></div>
        <div class="relative flex h-full flex-col justify-between p-12">
            <x-logo light size="lg" />
            <div>
                <p class="eyebrow !text-white/70">Admin Dashboard</p>
                <p class="mt-4 max-w-md font-display text-4xl font-extrabold leading-tight text-white">Plan. Build. <span class="text-brand">Deliver.</span> Together.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            <div class="mb-8 lg:hidden"><x-logo light /></div>
            <div class="rounded-3xl bg-white p-8 shadow-lift sm:p-10">
                <h1 class="text-2xl font-extrabold">Sign in</h1>
                <p class="mt-1 text-sm text-slate-500">Authorised staff only.</p>

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5" novalidate>
                    @csrf
                    <x-field name="email" type="email" label="Email" required placeholder="you@company.com" />
                    <x-field name="password" type="password" label="Password" required />
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Keep me signed in
                    </label>
                    <button type="submit" class="btn btn-primary w-full py-3.5">Sign in <x-icon name="arrow-right" class="size-4" /></button>
                </form>
            </div>
            <p class="mt-6 text-center text-sm text-white/60"><a href="{{ route('home') }}" class="hover:text-brand">← Back to website</a></p>
        </div>
    </div>
</div>
@livewireScriptConfig
</body>
</html>
