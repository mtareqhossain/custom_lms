<section>
    <header>
        <h2 class="display text-lg">Profile information</h2>
        <p class="muted mt-1 text-sm">Your name, email, and photo.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            @if($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover">
            @else
                <span class="avatar" style="width:4rem;height:4rem;font-size:1.25rem;">
                    {{ collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') }}
                </span>
            @endif
            <div>
                <label class="btn btn-quiet cursor-pointer !py-2 text-sm">
                    Change photo
                    <input type="file" name="avatar" accept="image/*" class="hidden">
                </label>
                @if($user->avatarUrl())
                    <label class="muted mt-2 flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_avatar" value="1"> Remove photo
                    </label>
                @endif
                @error('avatar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="name" class="text-sm font-medium">Name</label>
            <input id="name" name="name" type="text" class="field mt-1 w-full" value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" class="field mt-1 w-full" value="{{ old('email', $user->email) }}" required>
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="muted mt-2 text-sm">
                    Your email address is unverified.
                    <button form="send-verification" class="underline">Click here to re-send the verification email.</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-green-600">A new verification link has been sent.</p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button class="btn">Save</button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm text-green-600">Saved.</p>
            @endif
        </div>
    </form>

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
    @endif
</section>
