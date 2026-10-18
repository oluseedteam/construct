{{-- Browser-tab and home-screen icons. A favicon uploaded in Admin > Settings replaces the built-in logo icons. --}}
@php $customIcon = setting('favicon'); @endphp
@if ($customIcon)
    <link rel="icon" href="{{ media_url($customIcon) }}">
    <link rel="apple-touch-icon" href="{{ media_url($customIcon) }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endif
