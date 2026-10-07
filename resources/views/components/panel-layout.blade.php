@props(['title' => null])
@php
    $user = auth()->user();
    $role = $user->hasRole('business') ? 'business' : ($user->hasAnyRole(['admin', 'instructor']) ? 'staff' : 'student');

    // [label, route name or /path, icon, optional extra route patterns for the active state]
    $groups = match ($role) {
        'business' => ['Organization' => [
            ['Overview', 'business.dashboard', 'grid', ['business.dashboard', 'business.course']],
            ['Team', 'business.students', 'users'],
            ['Certificates', 'business.certificates', 'award'],
            ['Import students', 'business.import.create', 'upload'],
        ]],
        'staff' => ['Teaching' => [
            ['Dashboard', 'instructor.dashboard', 'grid', ['instructor.*']],
            ['Studio', '/admin', 'pencil'],
        ]],
        default => ['Learning' => [
            ['Dashboard', 'student.dashboard', 'home'],
            ['My courses', 'my.courses', 'book'],
            ['Certificates', 'student.certificates', 'award'],
            ['Orders', 'orders.index', 'receipt'],
        ]],
    };
    $groups['Explore'] = [['Browse courses', 'courses.index', 'compass']];

    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $roleLabel = ['business' => 'Business admin', 'staff' => $user->hasRole('admin') ? 'Administrator' : 'Instructor', 'student' => 'Student'][$role];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | ' : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.lms-styles')
</head>
<body class="antialiased" x-data="{ nav: false }" @keydown.escape.window="nav = false">
<div class="shell">
    <div class="scrim" x-show="nav" x-cloak @click="nav = false"></div>

    <aside class="sidebar" :class="{ 'open': nav }" aria-label="Main navigation">
        <div class="side-brand">
            <a href="{{ route('home') }}" class="display text-xl">{{ config('app.name') }}</a>
        </div>
        <nav class="side-scroll">
            @foreach($groups as $label => $items)
                <p class="side-label">{{ $label }}</p>
                @foreach($items as $item)
                    @php
                        $target = $item[1];
                        $isUrl = str_starts_with($target, '/');
                        $href = $isUrl ? url($target) : (Route::has($target) ? route($target) : null);
                        $patterns = $item[3] ?? [$target.'*'];
                        $active = ! $isUrl && request()->routeIs(...$patterns);
                    @endphp
                    @if($href)
                        <a href="{{ $href }}" class="side-link {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
                            <x-lms-icon :name="$item[2]" /> {{ $item[0] }}
                        </a>
                    @endif
                @endforeach
            @endforeach
        </nav>
        <div class="side-user">
            @if($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="" class="avatar" style="object-fit:cover;">
            @else
                <span class="avatar">{{ $initials }}</span>
            @endif
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                <p class="muted text-xs">{{ $roleLabel }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="muted" title="Log out" aria-label="Log out"><x-lms-icon name="logout" /></button>
            </form>
        </div>
    </aside>

    <div class="content">
        <div class="topbar">
            <button type="button" class="menu-btn btn btn-quiet !px-3 !py-2" @click="nav = true" aria-label="Open menu"><x-lms-icon name="menu" /></button>
            <p class="display hidden text-lg lg:block">{{ $title }}</p>
            <div class="flex items-center gap-3 text-sm">
                @if(Route::has('profile.edit'))<a href="{{ route('profile.edit') }}" class="muted inline-flex items-center gap-1"><x-lms-icon name="user" class="h-4 w-4" /> Profile</a>@endif
            </div>
        </div>
        @if(session('status'))
            <div class="bg-[var(--mint)] px-4 py-3 text-center text-sm font-medium text-[var(--teal-dark)]" role="status">{{ session('status') }}</div>
        @endif
        <main>{{ $slot }}</main>
    </div>
</div>
</body>
</html>
