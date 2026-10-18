<x-layouts.admin title="Company statistics">
    <p class="mb-6 max-w-2xl text-sm text-slate-600">Enter your real figures and tick <strong>Show on website</strong>. Statistics stay hidden until you do, so nothing is displayed that you haven't confirmed.</p>
    <form method="POST" action="{{ route('admin.stats.update') }}" class="space-y-5" novalidate>
        @csrf @method('PUT')
        @foreach ($stats as $stat)
            @php $p = "stats.{$stat->id}"; @endphp
            <fieldset class="card grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-6">
                <legend class="sr-only">{{ $stat->label }}</legend>
                <x-field :name="$p.'.label'" label="Label" :value="$stat->label" required class="lg:col-span-2" />
                <x-field :name="$p.'.value'" type="number" label="Value" :value="$stat->value" required />
                <x-field :name="$p.'.prefix'" label="Prefix" :value="$stat->prefix" placeholder="e.g. $" />
                <x-field :name="$p.'.suffix'" label="Suffix" :value="$stat->suffix" placeholder="e.g. +" />
                <div>
                    <label class="label" for="icon-{{ $stat->id }}">Icon</label>
                    <select id="icon-{{ $stat->id }}" name="stats[{{ $stat->id }}][icon]" class="input">
                        @foreach (['building', 'hardhat', 'handshake', 'users', 'check', 'shield', 'clock', 'home', 'city'] as $icon)<option value="{{ $icon }}" @selected(old($p.'.icon', $stat->icon) === $icon)>{{ ucfirst($icon) }}</option>@endforeach
                    </select>
                </div>
                <label class="flex items-center gap-3 text-sm font-medium text-navy sm:col-span-2 lg:col-span-6">
                    <input type="checkbox" name="stats[{{ $stat->id }}][is_visible]" value="1" @checked(old($p.'.is_visible', $stat->is_visible)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Show on website
                </label>
            </fieldset>
        @endforeach
        <button class="btn btn-primary">Save statistics</button>
    </form>
</x-layouts.admin>
