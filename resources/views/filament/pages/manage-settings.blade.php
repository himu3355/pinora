<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-filament::button type="submit" size="md">
                Save All Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
