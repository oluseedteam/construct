<section class="relative isolate overflow-hidden bg-navy-900 py-20 sm:py-24" aria-labelledby="cta-title">
    <img src="{{ media_url(content('home.cta_image'), 'images/demo/demo-4.jpg') }}" alt="" loading="lazy" class="absolute inset-0 -z-20 size-full object-cover opacity-40">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-navy-900 via-navy/90 to-navy/60"></div>
    <div class="container-x text-center" data-reveal>
        <h2 id="cta-title" class="mx-auto max-w-3xl text-3xl font-extrabold !text-white sm:text-5xl">{{ content('home.cta_title') }}</h2>
        <p class="mx-auto mt-5 max-w-xl text-lg text-white/80">{{ content('home.cta_text') }}</p>
        <div class="mt-9 flex flex-wrap justify-center gap-4">
            @if ($ctaUrl = page_url(['quote', 'contact']))
                <a href="{{ $ctaUrl }}" class="btn btn-primary px-8 py-3.5">{{ page_published('quote') ? content('home.cta_btn') : 'Contact Us' }} <x-icon name="arrow-right" class="size-4" /></a>
            @endif
            @if (setting('phone'))
                <a href="{{ tel_href(setting('phone')) }}" class="btn btn-ghost-light px-8 py-3.5"><x-icon name="phone" class="size-4" /> {{ setting('phone') }}</a>
            @elseif (page_published('contact') && page_published('quote'))
                <a href="{{ route('contact') }}" class="btn btn-ghost-light px-8 py-3.5">Contact Us</a>
            @endif
        </div>
    </div>
</section>
