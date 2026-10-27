<x-filament-panels::page>
    <div class="grid gap-6">
        <form wire:submit="analyze" class="grid gap-4">
            {{ $this->form }}

            <div>
                <x-filament::button
                    type="submit"
                    icon="heroicon-o-play"
                    wire:loading.attr="disabled"
                    wire:target="analyze"
                >
                    Proses Log
                </x-filament::button>
            </div>
        </form>

        @livewire(\App\Filament\Widgets\LatestScanResults::class, key('latest-scan-results'))
    </div>
</x-filament-panels::page>
