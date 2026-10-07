<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <script>document.documentElement.classList.add('js')</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.lms-styles')
</head>
<body class="min-h-screen antialiased">
<header class="border-b border-[var(--line)] bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('home') }}" class="display text-xl">{{ config('app.name') }}</a>
        <nav class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm font-medium">
            <a href="{{ route('courses.index') }}">Courses</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn !py-2">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}" class="btn !py-2">Create account</a>
            @endauth
        </nav>
    </div>
</header>
@if(session('status'))
    <div class="bg-[var(--mint)] px-4 py-3 text-center text-sm font-medium text-[var(--teal-dark)]" role="status">{{ session('status') }}</div>
@endif
<main>{{ $slot }}</main>
{{-- <footer class="mt-16 border-t border-[var(--line)] py-8 text-center text-sm muted">
    <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    <p class="mt-2 space-x-4">
        <a href="{{ route('legal.terms') }}" class="underline">Terms</a>
        <a href="{{ route('legal.privacy') }}" class="underline">Privacy</a>
        <a href="{{ route('legal.refund-policy') }}" class="underline">Refunds</a>
    </p>
</footer> --}}
@include('partials.site-footer')
<script>
    (function () {
        var els = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
        }, { threshold: 0.12 });
        els.forEach(function (e) { io.observe(e); });
    })();
</script>
</body>
</html>
