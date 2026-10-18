@props(['light' => false, 'size' => 'md', 'tagline' => false])
@php
    $logo = setting('logo');
    $name = (string) setting('company_name');
    // "R Square Construction" is shown stacked: "R SQUARE" over a spaced "CONSTRUCTION".
    $hasSuffix = preg_match('/^(.+?)\s+construction$/i', $name, $m);
    $line1 = mb_strtoupper($hasSuffix ? $m[1] : $name);
    $line2 = $hasSuffix ? 'Construction' : null;
    $markH = ['sm' => 'h-9', 'md' => 'h-11 sm:h-12', 'lg' => 'h-16'][$size] ?? 'h-11 sm:h-12';
    $text1 = ['sm' => 'text-lg', 'md' => 'text-xl sm:text-2xl', 'lg' => 'text-4xl'][$size] ?? 'text-xl sm:text-2xl';
@endphp
@if ($logo)
    {{-- Uploaded logo from Admin > Settings replaces the built-in mark + wordmark --}}
    <img src="{{ media_url($logo) }}" alt="{{ $name }}" class="{{ ['sm' => 'h-9', 'md' => 'h-11 sm:h-12', 'lg' => 'h-16'][$size] ?? 'h-11' }} w-auto" width="180" height="48">
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }} aria-label="{{ $name }}">
        <img src="{{ asset($light ? 'images/logo-mark-light.png' : 'images/logo-mark.png') }}" alt="" class="{{ $markH }} w-auto" width="224" height="160">
        <span class="flex flex-col leading-none">
            <span class="font-display {{ $text1 }} font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-navy' }}">{{ $line1 }}</span>
            @if ($line2)
                <span class="mt-1 font-display text-[0.5rem] font-semibold uppercase tracking-[0.5em] sm:text-[0.58rem] {{ $light ? 'text-white/85' : 'text-navy/85' }}">{{ $line2 }}</span>
            @endif
            @if ($tagline)
                <span class="mt-1.5 text-[0.55rem] font-medium uppercase tracking-[0.25em] {{ $light ? 'text-white/60' : 'text-slate-500' }}">{{ setting('tagline') }}</span>
            @endif
        </span>
    </span>
@endif
