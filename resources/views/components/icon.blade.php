@props(['name' => 'check', 'class' => 'size-5'])
@php
    [$type, $paths] = \App\Support\Icons::get($name);
@endphp
@if ($type === 'fill')
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">@foreach ($paths as $d)<path d="{{ $d }}"/>@endforeach</svg>
@else
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">@foreach ($paths as $d)<path d="{{ $d }}"/>@endforeach</svg>
@endif
