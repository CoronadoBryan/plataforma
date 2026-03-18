<?php

namespace App\Jobs;

use App\Models\Descarga;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessEnvatoDescarga implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public int $descargaId
    ) {}

    public function handle(): void
    {
        $descarga = Descarga::find($this->descargaId);

        if (! $descarga) {
            return;
        }

        $descarga->update([
            'estado' => 'procesando',
            'error_detalle' => null,
        ]);

        $scriptPath = base_path('automation/envato-download.mjs');

        if (! file_exists($scriptPath)) {
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => 'No existe automation/envato-download.mjs',
                'procesado_en' => now(),
            ]);

            return;
        }

        $process = new Process([
            'node',
            $scriptPath,
            '--url=' . $descarga->url,
            '--id=' . $descarga->id,
            '--downloads=' . storage_path('app/downloads'),
            '--auth=' . base_path('automation/.auth/envato.json'),
            '--headless=' . (filter_var(env('ENVATO_HEADLESS', true), FILTER_VALIDATE_BOOL) ? 'true' : 'false'),
        ], base_path(), null, null, 300);

        $process->run();

        if (! $process->isSuccessful()) {
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => trim($process->getErrorOutput() ?: $process->getOutput()),
                'procesado_en' => now(),
            ]);

            Log::warning('Envato download failed', [
                'descarga_id' => $descarga->id,
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);

            return;
        }

        $result = json_decode($process->getOutput(), true);

        if (is_array($result) && ($result['requiresVerification'] ?? false)) {
            $descarga->update([
                'estado' => 'requiere_verificacion',
                'error_detalle' => $result['message'] ?? 'Se requiere verificacion humana de Cloudflare.',
                'procesado_en' => now(),
            ]);

            return;
        }

        if (is_array($result) && ! ($result['ok'] ?? false)) {
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => $result['message'] ?? 'No se pudo completar la descarga.',
                'procesado_en' => now(),
            ]);

            return;
        }

        if (! is_array($result) || ! ($result['ok'] ?? false)) {
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => 'Respuesta invalida del script Playwright.',
                'procesado_en' => now(),
            ]);

            return;
        }

        $descarga->update([
            'estado' => 'completado',
            'archivo' => $result['filename'] ?? $descarga->archivo,
            'archivo_local' => $result['filePath'] ?? null,
            'procesado_en' => now(),
        ]);
    }
}
