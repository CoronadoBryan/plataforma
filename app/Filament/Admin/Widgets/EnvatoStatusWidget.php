<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class EnvatoStatusWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.envato-status-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public function isEnvatoActivo(): bool
    {
        $script = base_path('automation/envato-download.mjs');
        $auth = base_path('automation/.auth/envato.json');

        return file_exists($script) && file_exists($auth);
    }
}
