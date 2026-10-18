@props(['title', 'subtitle' => null, 'crumbs' => []])
<section class="relative isolate overflow-hidden bg-navy-900 pb-16 pt-32 sm:pb-20 sm:pt-40">
    <img src="{{ media_url(content('site.banner_image'), 'images/hero-1280.jpg') }}" alt="" class="absolute inset-0 -z-20 size-full object-cover object-[70%_40%]" fetchpriority="high">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-navy-900 via-navy/85 to-navy/45"></div>
    <div class="container-x">
        <nav aria-label="Breadcrumb" class="mb-5 flex flex-wrap items-center gap-2 text-sm text-white/70">
            <a href="{{ route('home') }}" class="hover:text-brand">Home</a>
            @foreach ($crumbs as $label => $url)
                <x-icon name="chevron-right" class="size-3.5" />
                @if ($url)
                    <a href="{{ $url }}" class="hover:text-brand">{{ $label }}</a>
                @else
                    <span class="text-white">{{ $label }}</span>
                @endif
            @endforeach
        </nav>
        <h1 class="max-w-3xl text-4xl font-extrabold !text-white sm:text-5xl animate-fade-up">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 max-w-2xl text-lg text-white/80 animate-fade-up [animation-delay:120ms]">{{ $subtitle }}</p>
        @endif
        <span class="mt-6 block h-1 w-20 rounded-full bg-brand"></span>
    </div>
</section>
