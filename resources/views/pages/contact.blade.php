<x-layouts.public title="Contact Us" :description="content('contact.hero_subtitle').' '.content('contact.intro_text')">
    <x-page-hero :title="content('contact.hero_title')" :subtitle="content('contact.hero_subtitle')" :crumbs="['Contact' => null]" />

    <section class="section">
        <div class="container-x grid gap-10 lg:grid-cols-5 lg:gap-14">
            <div class="space-y-6 lg:col-span-2" data-reveal="left">
                <div>
                    <p class="eyebrow">{{ content('contact.intro_eyebrow') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold">{{ content('contact.intro_title') }} <span class="text-brand">{{ content('contact.intro_accent') }}</span></h2>
                    <p class="mt-4 text-slate-600">{{ content('contact.intro_text') }}</p>
                </div>

                <ul class="space-y-4">
                    @if (setting('address'))
                        <li class="card flex gap-4 p-5"><span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand"><x-icon name="map-pin" class="size-6" /></span><div><p class="text-sm font-semibold text-navy">Office</p><p class="text-sm text-slate-600">{{ setting('address') }}</p></div></li>
                    @endif
                    @if (setting('phone'))
                        <li class="card flex gap-4 p-5"><span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand"><x-icon name="phone" class="size-6" /></span><div><p class="text-sm font-semibold text-navy">Phone</p><a href="{{ tel_href(setting('phone')) }}" class="text-sm text-slate-600 hover:text-brand">{{ setting('phone') }}</a></div></li>
                    @endif
                    @if (setting('email'))
                        <li class="card flex gap-4 p-5"><span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand"><x-icon name="mail" class="size-6" /></span><div><p class="text-sm font-semibold text-navy">Email</p><a href="mailto:{{ setting('email') }}" class="break-all text-sm text-slate-600 hover:text-brand">{{ setting('email') }}</a></div></li>
                    @endif
                    @if ($wa = whatsapp_href(setting('whatsapp')))
                        <li class="card flex gap-4 p-5"><span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand"><x-icon name="whatsapp" class="size-6" /></span><div><p class="text-sm font-semibold text-navy">WhatsApp</p><a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="text-sm text-slate-600 hover:text-brand">Chat with us</a></div></li>
                    @endif
                </ul>

                <div class="rounded-2xl bg-navy p-6 text-white">
                    <p class="font-display font-bold">Need a price?</p>
                    <p class="mt-1 text-sm text-white/75">Use our quotation form for project estimates.</p>
                    <a href="{{ route('quote') }}" class="btn btn-primary btn-sm mt-4">Request a Quote <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            </div>

            <div class="lg:col-span-3" data-reveal="right">
                <form method="POST" action="{{ route('contact.store') }}" class="card space-y-5 p-6 sm:p-9" novalidate x-data="{ sending: false }" @submit="sending = true">
                    @csrf
                    <h2 class="text-2xl font-extrabold">{{ content('contact.form_title') }}</h2>
                    <x-flash />

                    {{-- Honeypot --}}
                    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                        <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-field name="name" label="Full Name" required placeholder="Your full name" />
                        <x-field name="email" type="email" label="Email" required placeholder="you@example.com" />
                        <x-field name="phone" type="tel" label="Phone Number" placeholder="+1 555 000 0000" />
                        <x-field name="subject" label="Subject" required placeholder="How can we help?" />
                    </div>
                    <x-field name="message" type="textarea" :rows="6" label="Message" required placeholder="Tell us a little about what you need…" />

                    <button type="submit" class="btn btn-primary w-full sm:w-auto" :disabled="sending">
                        <span x-show="!sending" class="inline-flex items-center gap-2">Send Message <x-icon name="arrow-right" class="size-4" /></span>
                        <span x-show="sending" x-cloak>Sending…</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
