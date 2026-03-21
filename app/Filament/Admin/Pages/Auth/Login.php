<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        // Mantiene el estilo de Filament, solo cambia el texto.
        return new HtmlString('Acceso al panel de administración');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return new HtmlString('Verificación adicional para continuar.');
        }

        return new HtmlString('Ingresa tus credenciales para acceder a Michitech.');
    }

    public function content(Schema $schema): Schema
    {
        $appName = config('app.name', 'Michitech');

        $banner = new HtmlString(<<<HTML
<div class="mb-6 rounded-2xl border border-gray-200/70 bg-gradient-to-br from-primary-50/40 via-white to-white px-5 py-4 dark:border-white/10 dark:from-white/5 dark:via-gray-900/20 dark:to-gray-900/10">
    <div class="text-sm font-semibold text-gray-900 dark:text-white">{$appName}</div>
    <div class="mt-1 text-xs leading-5 text-gray-600 dark:text-gray-300">
        Panel de administración. Gestiona descargas y estados desde un solo lugar.
    </div>
</div>
HTML);

        $whatsappUrl = 'https://wa.me/51917080235?text=' . rawurlencode(
            'Hola, quiero pedir acceso al panel de administración de Michitech.'
        );

        $pedirAcceso = new HtmlString(View::make('filament.admin.pages.auth.login-pedir-acceso', [
            'whatsappUrl' => $whatsappUrl,
        ])->render());

        return $schema->components([
            RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
            Html::make($banner),
            $this->getFormContentComponent(),
            $this->getMultiFactorChallengeFormContentComponent(),
            Html::make($pedirAcceso),
            RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
        ]);
    }
}

