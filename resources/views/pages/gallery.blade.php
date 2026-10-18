<x-layouts.public title="Gallery" :description="content('gallery.hero_subtitle')">
    <x-page-hero :title="content('gallery.hero_title')" :subtitle="content('gallery.hero_subtitle')" :crumbs="['Gallery' => null]" />

    <section class="section" x-data="lightbox">
        <div class="container-x">
            <livewire:gallery-grid />
        </div>
        <x-lightbox-overlay />
    </section>

    <x-cta-band />
</x-layouts.public>
