<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
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

        $pedirAcceso = new HtmlString(<<<HTML
<div class="mt-6 flex justify-center">
    <a
        href="{$whatsappUrl}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 dark:bg-emerald-500 dark:hover:bg-emerald-400"
    >
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Pedir acceso
    </a>
</div>
<p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">
    Te abre WhatsApp para solicitar una cuenta.
</p>
HTML);

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

