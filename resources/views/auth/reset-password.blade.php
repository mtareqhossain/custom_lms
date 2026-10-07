<x-lms-layout title="Reset password">
<div class="mx-auto max-w-md px-4 py-16">
    <div class="panel p-8">
        <h1 class="display text-2xl">Set a new password</h1>

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="text-sm font-medium">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="field mt-1 w-full">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="text-sm font-medium">New password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="field mt-1 w-full">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-medium">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="field mt-1 w-full">
                @error('password_confirmation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="btn w-full">Reset password</button>
        </form>
    </div>
</div>
</x-lms-layout>
