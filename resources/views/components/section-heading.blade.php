@props(['eyebrow' => null, 'title', 'accent' => null, 'text' => null, 'center' => false, 'light' => false])
<div {{ $attributes->merge(['class' => ($center ? 'mx-auto max-w-2xl text-center ' : 'max-w-2xl ').'mb-10 sm:mb-14']) }} data-reveal>
    @if ($eyebrow)
        <p class="eyebrow {{ $light ? '!text-white/70' : '' }} {{ $center ? 'justify-center' : '' }}">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl lg:text-[2.6rem] {{ $light ? '!text-white' : '' }}">
        {{ $title }}@if ($accent) <span class="text-brand">{{ $accent }}</span>@endif
    </h2>
    @if ($text)
        <p class="mt-4 text-base leading-relaxed {{ $light ? 'text-white/75' : 'text-slate-600' }}">{{ $text }}</p>
    @endif
</div>
