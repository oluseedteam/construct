@props(['status'])
@php
    $colors = [
        'new' => 'bg-brand-50 text-brand-700',
        'read' => 'bg-sky-100 text-sky-800',
        'reviewing' => 'bg-sky-100 text-sky-800',
        'quoted' => 'bg-violet-100 text-violet-800',
        'accepted' => 'bg-emerald-100 text-emerald-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        'ongoing' => 'bg-brand-50 text-brand-700',
        'planned' => 'bg-slate-200 text-slate-700',
        'declined' => 'bg-red-100 text-red-800',
        'archived' => 'bg-slate-200 text-slate-600',
        'published' => 'bg-emerald-100 text-emerald-800',
        'draft' => 'bg-slate-200 text-slate-600',
        'hidden' => 'bg-amber-100 text-amber-800',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'badge capitalize '.($colors[$status] ?? 'bg-slate-100 text-slate-700')]) }}>{{ $status }}</span>
