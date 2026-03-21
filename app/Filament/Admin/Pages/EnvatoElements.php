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

    /**
     * Filament Shield solo descubre automáticamente Resources; esta Page no tenía
     * autorización, por eso cualquier usuario autenticado la veía en el menú.
     *
     * Permiso: view_envato_elements (definido en config/filament-shield.php → custom_permissions).
     * Asígnalo solo a los roles que deben usar Envato Elements.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('view_envato_elements');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public ?string $link = null;
    public ?int $descargaId = null;

    public bool $mostrarCompletado = true;

    public function limpiar(): void
    {
        $this->descargaId = null;
        $this->mostrarCompletado = false;
        $this->reset('link');

        Notification::make()
            ->title('Listo para una nueva descarga.')
            ->success()
            ->send();
    }

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

        if ($this->descargaEnCurso) {
            $this->descargaId = $this->descargaEnCurso->id;

            Notification::make()
                ->title('Ya tienes una descarga en proceso.')
                ->body('Espera a que termine antes de ejecutar otra.')
                ->warning()
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
        $this->mostrarCompletado = true;

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

    public function getDescargaEnCursoProperty(): ?Descarga
    {
        if (! auth()->check()) {
            return null;
        }

        return Descarga::query()
            ->where('user_id', auth()->id())
            ->whereIn('estado', ['pendiente', 'procesando'])
            ->latest('id')
            ->first();
    }

    public function getPuedeEjecutarProperty(): bool
    {
        return $this->descargaEnCurso === null;
    }

    public function getProgresoProperty(): int
    {
        $estado = $this->descargaActual?->estado;

        return match ($estado) {
            'pendiente' => 20,
            'procesando' => 60,
            'completado' => 100,
            'requiere_verificacion' => 90,
            'error' => 100,
            default => 0,
        };
    }

    public function getEstadoTextoProperty(): string
    {
        $estado = $this->descargaActual?->estado;

        return match ($estado) {
            'pendiente' => 'En cola',
            'procesando' => 'Descargando',
            'completado' => 'Completado',
            'requiere_verificacion' => 'Requiere verificacion',
            'error' => 'Error',
            default => 'Sin actividad',
        };
    }
}
