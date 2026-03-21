<x-filament-widgets::widget>
    <x-filament::section>
        @if (! $this->tienePermisoEnvato())
            {{-- Sin permiso: nunca mostrar como "activo" verde --}}
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex h-3 w-3 shrink-0 rounded-full bg-gray-400 ring-2 ring-gray-400/30 dark:bg-gray-500"
                    title="Sin acceso"
                ></span>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Envato inactivo
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        No tienes permiso para usar el módulo de descargas Envato.
                    </p>
                </div>
            </div>
        @elseif ($this->isEnvatoActivo())
            <div class="envato-status-widget--activo flex flex-wrap items-center gap-3 rounded-xl px-4 py-3 shadow-sm">
                <span
                    class="envato-status-widget--dot inline-flex h-3.5 w-3.5 shrink-0 rounded-full ring-2 ring-[#34d399]/60"
                    title="Operativo"
                ></span>
                <div>
                    <p class="envato-status-widget--titulo text-sm font-bold">
                        Envato activo
                    </p>
                    <p class="envato-status-widget--sub mt-0.5 text-xs">
                        Tienes acceso y el servidor está listo para descargas.
                    </p>
                </div>
            </div>
            <style>
                .envato-status-widget--activo {
                    background: linear-gradient(135deg, #ecfdf5 0%, #a7f3d0 100%);
                    border: 2px solid #10b981;
                }
                .envato-status-widget--dot {
                    background-color: #059669;
                }
                .envato-status-widget--titulo {
                    color: #047857;
                }
                .envato-status-widget--sub {
                    color: #059669;
                }
                .dark .envato-status-widget--activo {
                    background: linear-gradient(135deg, #022c22 0%, #065f46 100%);
                    border-color: #34d399;
                }
                .dark .envato-status-widget--dot {
                    background-color: #34d399;
                }
                .dark .envato-status-widget--titulo {
                    color: #ecfdf5;
                }
                .dark .envato-status-widget--sub {
                    color: #a7f3d0;
                }
            </style>
        @else
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex h-3 w-3 shrink-0 rounded-full bg-amber-500 ring-2 ring-amber-500/30"
                    title="Revisar configuración"
                ></span>
                <div>
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">
                        Envato no configurado
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Tienes acceso, pero falta el script o la sesión <code class="rounded bg-gray-100 px-1 py-0.5 text-[0.7rem] dark:bg-white/10">envato.json</code> en el servidor.
                    </p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
