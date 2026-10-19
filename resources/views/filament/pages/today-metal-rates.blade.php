<x-filament-panels::page>
    <div class="space-y-6">

        <!-- Top Overview & Quick Controls Card -->
        <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gradient-to-r from-amber-500/5 via-white to-amber-500/5 dark:from-amber-950/20 dark:via-gray-900 dark:to-amber-950/20 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="p-3 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-currency-rupee" class="h-7 w-7" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white flex items-center gap-2">
                        <span>All-in-One Daily Metal Rate Entry</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                            Live Pricing
                        </span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        {{ now()->format('l, d F Y') }} · Rates entered here instantly recalculate product prices across the entire catalog.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($this->hasSyncedToday())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-xs">
                        <x-filament::icon icon="heroicon-m-check-badge" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        Synced Today ({{ $this->getLastSyncedTime() }})
                    </span>
                @endif

                <x-filament::button
                    type="button"
                    color="gray"
                    size="sm"
                    icon="heroicon-m-document-duplicate"
                    wire:click="copyPreviousRates"
                    tooltip="Copy rates from previous records into empty inputs"
                >
                    Pre-fill Latest Rates
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="{{ $this->hasSyncedToday() ? 'gray' : 'success' }}"
                    size="sm"
                    icon="heroicon-m-arrow-path"
                    wire:click="syncFromGoldApi"
                    tooltip="{{ $this->hasSyncedToday() ? 'Re-sync live market rates (cached 60s to protect IP from blocking)' : 'Fetch live market bullion rates from Gold-API.com (cached 60s to protect IP from blocking)' }}"
                >
                    {{ $this->hasSyncedToday() ? 'Re-sync API' : 'Sync Live API' }}
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="danger"
                    size="sm"
                    variant="outlined"
                    icon="heroicon-m-backspace"
                    wire:click="clearForm"
                >
                    Clear All
                </x-filament::button>
            </div>
        </div>

        <!-- Main Form -->
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button type="submit" size="lg" color="primary" icon="heroicon-o-check">
                    Save Today's Rates
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="lg"
                    color="gray"
                    variant="outlined"
                    icon="heroicon-o-table-cells"
                    wire:click="save(true)"
                >
                    Save & View History
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="lg"
                    color="gray"
                    variant="ghost"
                    tag="a"
                    href="{{ \App\Filament\Resources\MetalRates\MetalRateResource::getUrl('index') }}"
                >
                    Cancel
                </x-filament::button>
            </div>
        </form>

    </div>
</x-filament-panels::page>
