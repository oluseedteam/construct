<x-layouts.public title="Projects" :description="content('projects.hero_subtitle').' Residential, commercial, infrastructure and renovation work.'">
    <x-page-hero :title="content('projects.hero_title')" :subtitle="content('projects.hero_subtitle')" :crumbs="['Projects' => null]" />

    <section class="section bg-mist">
        <div class="container-x">
            <livewire:project-filter />
        </div>
    </section>

    <x-cta-band />
</x-layouts.public>
