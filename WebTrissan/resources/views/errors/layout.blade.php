<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, sans-serif; color: #0a192f; background: #f8fafc; }
        main { max-width: 28rem; padding: 1.5rem; text-align: center; }
        h1 { margin: 0 0 .5rem; font-size: 1.5rem; }
        p { margin: 0 0 1.5rem; color: #64748b; line-height: 1.6; }
        a { display: inline-block; padding: .75rem 1.25rem; border-radius: .5rem; background: #0a192f; color: #fff; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Kembali ke beranda</a>
    </main>
</body>
</html>
