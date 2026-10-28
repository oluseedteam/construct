<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8" novalidate>
        @csrf @method('PUT')

        <section class="card space-y-5 p-6 sm:p-8">
            <h2 class="text-lg font-bold">Company</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <x-field name="company_name" label="Company name" :value="setting('company_name')" required />
                <x-field name="tagline" label="Tagline / slogan" :value="setting('tagline')" hint="Shown in the footer and under the homepage image." />
                <x-field name="email" type="email" label="Email address" :value="setting('email')" hint="Contact form and quote notifications are sent here. If empty, they go to admin accounts." />
                <x-field name="phone" label="Phone number" :value="setting('phone')" />
                <x-field name="whatsapp" label="WhatsApp number" :value="setting('whatsapp')" hint="International format, e.g. +2348012345678. Shows a WhatsApp button on the site." />
                <x-field name="address" type="textarea" :rows="3" label="Office address" :value="setting('address')" class="md:col-span-2" />
            </div>
        </section>

        <section class="card space-y-5 p-6 sm:p-8">
            <h2 class="text-lg font-bold">Logo</h2>
            <div class="flex flex-wrap items-center gap-8">
                <div class="rounded-xl bg-mist p-6 ring-1 ring-line"><x-logo /></div>
                <div class="min-w-64 flex-1 space-y-3">
                    <x-field name="logo" type="file" label="Upload logo" accept="image/png,image/jpeg,image/webp" hint="PNG with a transparent background works best. Until you upload one, a text wordmark is shown." />
                    @if (setting('logo'))<label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_logo" value="1" class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Remove uploaded logo</label>@endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-8 border-t border-line pt-5">
                <img src="{{ setting('favicon') ? media_url(setting('favicon')) : asset('favicon-192.png') }}" alt="Current site icon" class="size-16 rounded-xl object-contain ring-1 ring-line">
                <div class="min-w-64 flex-1 space-y-3">
                    <x-field name="favicon" type="file" label="Site icon (favicon)" accept="image/png,image/jpeg,image/webp" hint="Square PNG, at least 64×64 (192×192 recommended). Shown in browser tabs and on phone home screens. The R Square roof-and-R icon is used until you upload one." />
                    @if (setting('favicon'))<label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_favicon" value="1" class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Remove uploaded icon (use the default)</label>@endif
                </div>
            </div>
        </section>

        <section class="card space-y-5 p-6 sm:p-8">
            <h2 class="text-lg font-bold">Social media</h2>
            <p class="text-sm text-slate-500">Full URLs including https://. Empty links are not shown.</p>
            <div class="grid gap-5 md:grid-cols-2">
                <x-field name="facebook" type="url" label="Facebook" :value="setting('facebook')" placeholder="https://facebook.com/…" />
                <x-field name="instagram" type="url" label="Instagram" :value="setting('instagram')" placeholder="https://instagram.com/…" />
                <x-field name="linkedin" type="url" label="LinkedIn" :value="setting('linkedin')" placeholder="https://linkedin.com/company/…" />
                <x-field name="x_twitter" type="url" label="X (Twitter)" :value="setting('x_twitter')" placeholder="https://x.com/…" />
                <x-field name="youtube" type="url" label="YouTube" :value="setting('youtube')" placeholder="https://youtube.com/@…" />
                <x-field name="tiktok" type="url" label="TikTok" :value="setting('tiktok')" placeholder="https://tiktok.com/@…" />
            </div>
        </section>

        <section class="card space-y-5 p-6 sm:p-8">
            <h2 class="text-lg font-bold">SEO</h2>
            <x-field name="seo_title" label="Homepage title" :value="setting('seo_title')" hint="Up to 70 characters." />
            <x-field name="seo_description" type="textarea" :rows="3" label="Default meta description" :value="setting('seo_description')" hint="Up to 200 characters. Used where a page has no description of its own." />
            <x-field name="seo_image" type="file" label="Default social sharing image" accept="image/*" hint="Shown when links are shared on social media (1200×630 recommended)." />
            @if (setting('seo_image'))
                <img src="{{ media_url(setting('seo_image')) }}" alt="Current sharing image" class="h-28 w-auto rounded-xl object-cover ring-1 ring-line">
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_seo_image" value="1" class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Remove sharing image</label>
            @endif
        </section>

        <button class="btn btn-primary">Save settings</button>
    </form>
</x-layouts.admin>
