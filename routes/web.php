<?php

use App\Models\Descarga;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/descargas/{descarga}/archivo', function (Request $request, Descarga $descarga) {
    Log::warning('descargas.archivo hit', [
        'descarga_id' => $descarga->id,
        'request_url' => $request->fullUrl(),
        'scheme' => $request->getScheme(),
        'is_secure' => $request->isSecure(),
        'x_forwarded_proto' => $request->header('x-forwarded-proto'),
        'auth_id' => auth()->id(),
        'archivo_local' => $descarga->archivo_local,
        'archivo_local_exists' => $descarga->archivo_local ? file_exists($descarga->archivo_local) : false,
    ]);

    // Importante: detrás de proxy/Cloudflare, Laravel a veces ve http pero el header indica https.
    // Usamos el "forwarded proto" como fuente de verdad para evitar redirecciones en bucle.
    $forwardedProto = strtolower((string) $request->header('x-forwarded-proto', ''));
    $effectiveSecure = $request->isSecure() || $forwardedProto === 'https' || $forwardedProto === 'wss';

    if (! $effectiveSecure) {
        Log::warning('descargas.archivo redirect seguro', [
            'forwardedProto' => $forwardedProto,
            'request_scheme' => $request->getScheme(),
        ]);

        return redirect()->secure($request->getRequestUri(), 302);
    }

    if ($descarga->user_id !== auth()->id()) {
        abort(403);
    }
    if (! $descarga->archivo_local || ! file_exists($descarga->archivo_local)) {
        abort(404);
    }

    $fileSize = null;
    try {
        $fileSize = file_exists($descarga->archivo_local) ? filesize($descarga->archivo_local) : null;
    } catch (\Throwable $e) {}

    Log::warning('descargas.archivo preparando download', [
        'descarga_id' => $descarga->id,
        'file_size' => $fileSize,
        'is_readable' => $descarga->archivo_local ? is_readable($descarga->archivo_local) : null,
        'archivo_local' => $descarga->archivo_local,
    ]);

    try {
        return response()->download($descarga->archivo_local, basename($descarga->archivo_local));
    } catch (\Throwable $e) {
        Log::error('descargas.archivo error en response()->download', [
            'descarga_id' => $descarga->id,
            'error' => $e->getMessage(),
        ]);

        abort(500);
    }
})->name('descargas.archivo');
