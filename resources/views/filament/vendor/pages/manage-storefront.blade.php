<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4 pt-4">
            <x-filament::button type="submit" size="sm">
                Save Changes
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
