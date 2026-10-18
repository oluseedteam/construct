<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ url('favicon.ico') }}" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ url('apple-touch-icon.png') }}">
    <title>@yield('title') | {{ config('app.name') }}</title>
    {{-- Self-contained styles: error pages must render even if assets or the database are unavailable --}}
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:#082c50;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#fff;text-align:center}
        .card{max-width:520px}.code{font-size:clamp(5rem,22vw,9rem);font-weight:900;line-height:1;letter-spacing:-.04em;color:#ff6412;margin:0}
        h1{font-size:1.75rem;margin:.5rem 0 .75rem}p{color:rgba(255,255,255,.75);line-height:1.6;margin:0 0 2rem}
        a{display:inline-block;background:#ff6412;color:#fff;text-decoration:none;font-weight:600;padding:.85rem 1.75rem;border-radius:999px}a:hover{background:#e9540a}
        .brand{font-weight:900;letter-spacing:.02em;margin-bottom:2rem;font-size:1.5rem}.brand b{color:#ff6412}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand"><b>R</b>SQUARE <span style="font-size:.6rem;letter-spacing:.5em;font-weight:600;opacity:.8">CONSTRUCTION</span></div>
        <p class="code">@yield('code')</p>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Back to homepage</a>
    </div>
</body>
</html>
