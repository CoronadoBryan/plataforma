<x-filament-panels::page wire:poll.2s>
    <x-filament::section class="max-w-2xl">
        <form wire:submit="ejecutar" style="display: flex; flex-direction: column;">
            <div class="fi-fo-field-wrp">
                <x-filament::input.wrapper :valid="! $errors->has('link')">
                    <input
                        type="url"
                        wire:model.defer="link"
                        placeholder="https://elements.envato.com/..."
                        class="fi-input"
                        dusk="envato-link-input"
                    />
                </x-filament::input.wrapper>
                @error('link')
                    <p class="fi-fo-field-wrp-error-message mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div style=" padding-top: 2rem;">
            <x-filament::button
                type="submit"
                color="primary"
                icon="heroicon-o-cloud-arrow-down"
                size="lg"
                :disabled="! $this->puedeEjecutar"
                wire:loading.attr="disabled"
                wire:target="ejecutar"
            >
                <x-filament::loading-indicator wire:loading wire:target="ejecutar" class="size-5 shrink-0" />
                <span wire:loading.remove wire:target="ejecutar">Descargar</span>
                <span wire:loading wire:target="ejecutar">Procesando...</span>
            </x-filament::button>
            </div>
        </form>

        @if ($this->descargaActual && in_array($this->descargaActual->estado, ['pendiente', 'procesando']))
            <div class="mt-10 space-y-3">
                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>{{ $this->estadoTexto }}</span>
                    <span class="font-medium text-primary-600 dark:text-primary-400">{{ $this->progreso }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
                    <div
                        class="h-full rounded-lg bg-primary-500 transition-all duration-500"
                        style="width: {{ $this->progreso }}%;"
                    ></div>
                </div>
            </div>
        @endif

        @if ($this->mostrarCompletado && $this->descargaActual && $this->descargaActual->estado === 'completado' && $this->descargaActual->archivo_local)
            <div class="mt-10 flex flex-wrap gap-3">
                <x-filament::button
                    tag="a"
                    :href="'/descargas/' . $this->descargaActual->id . '/archivo'"
                    color="success"
                    icon="heroicon-o-arrow-down-tray"
                    size="lg"
                >
                    Descargar archivo
                </x-filament::button>
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-arrow-path"
                    size="lg"
                    wire:click="limpiar"
                >
                    Nueva descarga
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
