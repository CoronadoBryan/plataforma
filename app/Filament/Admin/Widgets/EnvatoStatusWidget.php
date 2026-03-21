<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class EnvatoStatusWidget extends Widget
{
    /** @var view-string */
    protected string $view = 'filament.admin.widgets.envato-status-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    /**
     * Servidor con script y sesión listos (independiente del usuario).
     */
    public function isEnvatoActivo(): bool
    {
        $script = base_path('automation/envato-download.mjs');
        $auth = base_path('automation/.auth/envato.json');

        return file_exists($script) && file_exists($auth);
    }

    /**
     * Mismo criterio que la página Envato Elements (Filament Shield).
     */
    public function tienePermisoEnvato(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('View:EnvatoElements')
            || $user->can('view_envato_elements');
    }
}
