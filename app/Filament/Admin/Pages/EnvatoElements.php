<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\ProcessEnvatoDescarga;
use App\Models\Descarga;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EnvatoElements extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-down';

    protected static ?string $navigationLabel = 'Envato Elements';

    protected static ?string $title = 'Envato Elements';

    protected string $view = 'filament.admin.pages.envato-elements';

    public ?string $link = null;
    public ?int $descargaId = null;

    public function ejecutar(): void
    {
        $this->validate([
            'link' => ['required', 'url'],
        ]);

        $userId = auth()->id();

        if (! $userId) {
            Notification::make()
                ->title('No hay usuario autenticado.')
                ->danger()
                ->send();

            return;
        }

        $descarga = Descarga::create([
            'user_id' => $userId,
            'archivo' => $this->resolverNombreArchivo($this->link),
            'url' => $this->link,
            'estado' => 'pendiente',
        ]);

        $this->descargaId = $descarga->id;

        ProcessEnvatoDescarga::dispatch($descarga->id);

        $this->reset('link');

        Notification::make()
            ->title('Registro creado. Proceso de descarga en cola.')
            ->success()
            ->send();
    }

    protected function resolverNombreArchivo(string $link): string
    {
        $path = parse_url($link, PHP_URL_PATH) ?? '';
        $archivo = basename($path);

        return $archivo !== '' && $archivo !== '/' ? $archivo : 'envato-elements';
    }

    public function getDescargaActualProperty(): ?Descarga
    {
        if (! auth()->check()) {
            return null;
        }

        if ($this->descargaId) {
            return Descarga::find($this->descargaId);
        }

        return Descarga::query()
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();
    }

    public function getProgresoProperty(): int
    {
        $estado = $this->descargaActual?->estado;

        return match ($estado) {
            'pendiente' => 25,
            'procesando' => 65,
            'completado' => 100,
            'requiere_verificacion' => 100,
            'error' => 100,
            default => 0,
        };
    }
}
