<x-layouts.public title="General Application" :description="content('careers.empty_text')">
    <x-page-hero :title="content('careers.hero_title')" subtitle="Send us a general application." :crumbs="['Careers' => route('careers.index'), 'General application' => null]" />

    <section class="section bg-mist">
        <div class="container-x max-w-3xl">
            <x-application-form />
        </div>
    </section>
</x-layouts.public>
