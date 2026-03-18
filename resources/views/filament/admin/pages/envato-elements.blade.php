<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2" wire:poll.3s>
        <form wire:submit="ejecutar" class="space-y-4">
            <div>
                <label for="envato-link" class="mb-1 block text-sm font-medium text-gray-900 dark:text-gray-100">
                    Link de Envato Elements
                </label>
                <input
                    id="envato-link"
                    type="url"
                    wire:model.defer="link"
                    placeholder="https://elements.envato.com/..."
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                />
                @error('link')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit">
                Ejecutar
            </x-filament::button>
        </form>

        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="text-sm font-semibold">Estado de descarga</h3>

            @if ($this->descargaActual)
                <div class="mt-3 space-y-2 text-sm">
                    <p><span class="font-medium">Estado:</span> {{ $this->descargaActual->estado }}</p>
                    <p><span class="font-medium">Archivo:</span> {{ $this->descargaActual->archivo }}</p>
                    <p>
                        <span class="font-medium">Archivo local:</span>
                        {{ $this->descargaActual->archivo_local ? basename($this->descargaActual->archivo_local) : '-' }}
                    </p>
                    @if ($this->descargaActual->error_detalle)
                        <p class="{{ $this->descargaActual->estado === 'requiere_verificacion' ? 'text-warning-600' : 'text-danger-600' }}">
                            <span class="font-medium">{{ $this->descargaActual->estado === 'requiere_verificacion' ? 'Atencion:' : 'Error:' }}</span>
                            {{ $this->descargaActual->error_detalle }}
                        </p>
                    @endif
                </div>

                <div class="mt-4">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span>Progreso</span>
                        <span>{{ $this->progreso }}%</span>
                    </div>
                    <div class="h-2 w-full rounded bg-gray-200 dark:bg-gray-700">
                        <div
                            class="h-2 rounded transition-all duration-300 {{ $this->descargaActual->estado === 'error' ? 'bg-danger-500' : ($this->descargaActual->estado === 'requiere_verificacion' ? 'bg-warning-500' : 'bg-primary-600') }}"
                            style="width: {{ $this->progreso }}%;"
                        ></div>
                    </div>
                </div>
            @else
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Todavia no hay descargas para mostrar.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
