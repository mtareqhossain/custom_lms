<x-filament-panels::page>
    <form wire:submit="send">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Send campaign
            </x-filament::button>
        </div>
    </form>

    <div class="mt-10">
        <h3 class="text-base font-semibold mb-3">Recent campaigns</h3>
        <div class="divide-y divide-gray-200 dark:divide-gray-700 rounded-xl border border-gray-200 dark:border-gray-700">
            @foreach (\App\Models\EmailCampaign::latest()->limit(10)->get() as $campaign)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $campaign->name }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $campaign->template?->name ?? 'Deleted template' }} &middot;
                            {{ ucfirst(str_replace('_', ' ', $campaign->audience_type)) }}
                        </div>
                    </div>
                    <div class="text-sm text-right">
                        <div>{{ $campaign->sent_count }} / {{ $campaign->recipients_count }} sent</div>
                        <div class="text-gray-500">{{ ucfirst($campaign->status) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
