@php $editing = $testimonial->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit testimonial' : 'New testimonial'">
    <a href="{{ route('admin.testimonials.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All testimonials</a>
    <form method="POST" novalidate class="card mx-auto max-w-2xl space-y-5 p-6 sm:p-8"
          action="{{ $editing ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="author_name" label="Client name" :value="$testimonial->author_name" required />
            <x-field name="author_role" label="Role / company" :value="$testimonial->author_role" />
        </div>
        <x-field name="content" type="textarea" :rows="5" label="Testimonial" :value="$testimonial->content" required />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="rating" type="select" label="Rating" :options="[5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']" :value="$testimonial->rating" required />
            <x-field name="sort_order" type="number" label="Display order" :value="$testimonial->sort_order ?? 0" />
        </div>
        <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_approved" value="0"><input type="checkbox" name="is_approved" value="1" @checked(old('is_approved', $testimonial->is_approved)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Approved (show on the website). Confirm the client has agreed to this being published.</label>
        <button class="btn btn-primary">{{ $editing ? 'Save changes' : 'Save testimonial' }}</button>
    </form>
</x-layouts.admin>
