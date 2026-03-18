<?php

use App\Models\Descarga;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/descargas/{descarga}/archivo', function (Descarga $descarga) {
    if ($descarga->user_id !== auth()->id()) {
        abort(403);
    }
    if (! $descarga->archivo_local || ! file_exists($descarga->archivo_local)) {
        abort(404);
    }

    return response()->download($descarga->archivo_local, basename($descarga->archivo_local));
})->name('descargas.archivo');
