<x-filament-widgets::widget>
    <x-filament::section>
        @if ($this->isEnvatoActivo())
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex h-3 w-3 shrink-0 rounded-full bg-emerald-500 ring-2 ring-emerald-500/30"
                    title="Operativo"
                ></span>
                <div>
                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                        Envato activo
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Módulo de descargas listo (script y sesión configurados).
                    </p>
                </div>
            </div>
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
                        Falta el script de automatización o la sesión <code class="rounded bg-gray-100 px-1 py-0.5 text-[0.7rem] dark:bg-white/10">envato.json</code>.
                    </p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
