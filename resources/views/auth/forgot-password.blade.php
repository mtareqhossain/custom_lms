<x-lms-layout title="Forgot your password?">
<div class="mx-auto max-w-md px-4 py-16">
    <div class="panel p-8">
        <h1 class="display text-2xl">Forgot your password?</h1>
        <p class="muted mt-2 text-sm">No worries. Enter your email and we'll send you a link to reset it.</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-[var(--mint)] p-3 text-sm font-medium text-[var(--teal-dark)]">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="text-sm font-medium">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="field mt-1 w-full">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="btn w-full">Email password reset link</button>
        </form>

        <p class="muted mt-6 text-center text-sm"><a href="{{ route('login') }}" class="underline">Back to login</a></p>
    </div>
</div>
</x-lms-layout>
