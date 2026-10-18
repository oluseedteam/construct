<x-layouts.admin title="Website content">
    <p class="mb-6 max-w-3xl text-sm text-slate-600">Edit the text and images shown across the website, one page at a time. Clear a text field to restore its default wording. Projects, services, gallery, statistics, testimonials, highlights and careers have their own sections in the menu.</p>

    <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" novalidate x-data="{ tab: @js(array_key_first($tabs)) }">
        @csrf @method('PUT')

        <div class="-mx-4 mb-6 overflow-x-auto px-4 sm:mx-0 sm:px-0" role="tablist" aria-label="Website sections">
            <div class="flex w-max gap-2">
                @foreach ($tabs as $name => $fields)
                    <button type="button" role="tab" @click="tab = @js($name)" :aria-selected="tab === @js($name)"
                            class="whitespace-nowrap rounded-full px-5 py-2 text-sm font-semibold transition"
                            :class="tab === @js($name) ? 'bg-navy text-white shadow-lg' : 'bg-white text-navy ring-1 ring-line hover:text-brand'">{{ $name }}</button>
                @endforeach
            </div>
        </div>

        @foreach ($tabs as $name => $fields)
            {{-- All tabs stay in the DOM so one Save button stores every change. --}}
            <section x-show="tab === @js($name)" @if (! $loop->first) x-cloak @endif role="tabpanel" class="card space-y-5 p-6 sm:p-8" aria-label="{{ $name }}">
                <h2 class="text-lg font-bold">{{ $name }}</h2>
                @foreach ($fields as $key => [$label, $type, $max, $default])
                    @php $field = \App\Http\Controllers\Admin\ContentController::field($key); @endphp
                    @if ($type === 'image')
                        <div class="grid items-start gap-4 border-t border-line pt-5 first:border-0 first:pt-0 md:grid-cols-3">
                            <img src="{{ media_url(content($key), $default) }}" alt="Current: {{ $label }}" class="h-32 w-full rounded-xl object-cover ring-1 ring-line md:col-span-1">
                            <div class="space-y-3 md:col-span-2">
                                <x-field :name="$field" type="file" :label="$label" accept="image/*" hint="JPG, PNG or WebP up to 5 MB. Use a clean photo with no text or logos baked in." />
                                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="reset__{{ $field }}" value="1" class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Restore the default image</label>
                            </div>
                        </div>
                    @else
                        <x-field :name="$field" :type="$type" :rows="$type === 'textarea' ? ($max > 1000 ? 9 : 3) : 4" :label="$label" :value="content($key)" :hint="'Up to '.$max.' characters.'" />
                    @endif
                @endforeach
            </section>
        @endforeach

        <div class="mt-6 flex items-center gap-4">
            <button class="btn btn-primary">Save all content</button>
            <a href="{{ route('home') }}" target="_blank" class="text-sm font-semibold text-brand hover:underline">View website ↗</a>
        </div>
    </form>
</x-layouts.admin>
