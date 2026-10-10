{{-- resources/views/admin/settings/site.blade.php --}}
<x-panel-layout>
<div class="mx-auto max-w-2xl px-4 py-8">
    <h1 class="display text-2xl">Site settings</h1>

    @if(session('status'))
        <div class="panel mt-4 p-4 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.site.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="font-medium text-sm">App name</label>
            <input type="text" name="site_name" value="{{ old('site_name', $settings->site_name) }}"
                   class="block mt-1 w-full rounded-md border-gray-300" required>
        </div>

        <div>
            <label class="font-medium text-sm">Logo</label>

            @if($settings->logo_path)
                <div class="mt-2 flex items-center gap-4">
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" class="h-10" alt="Current logo">
                    <label class="text-sm text-red-600">
                        <input type="checkbox" name="remove_logo" value="1"> Remove logo (use text name instead)
                    </label>
                </div>
            @else
                <p class="muted text-sm mt-1">No logo set — showing app name as text.</p>
            @endif

            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="block mt-2">
        </div>

        <button type="submit" class="btn">Save</button>
    </form>
</div>
</x-panel-layout>
