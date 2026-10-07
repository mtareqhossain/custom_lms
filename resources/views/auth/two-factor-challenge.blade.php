<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        We emailed you a 6-digit code. Enter it below to finish signing in. The code expires in 10 minutes.
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" value="Login code" />
            <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-widest" type="text"
                name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Verify &amp; sign in
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-500 underline hover:text-gray-800">
            Didn't get a code? Resend it
        </button>
    </form>
</x-guest-layout>
