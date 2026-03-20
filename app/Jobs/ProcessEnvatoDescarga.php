<?php

namespace App\Jobs;

use App\Models\Descarga;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
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
        $authPath = base_path('automation/.auth/envato.json');
        $headless = config('services.envato.headless', true);

        if (! file_exists($scriptPath)) {
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => 'No existe automation/envato-download.mjs',
                'procesado_en' => now(),
            ]);

            return;
        }

        $nodeBinary = env('NODE_PATH', 'node');
        $downloadsPath = storage_path('app/downloads');
        $headlessArg = $headless ? 'true' : 'false';

        // En Windows con headless=false: ejecutar via "start /wait" para que Chromium abra en ventana visible
        $outputFile = null;
        if (PHP_OS_FAMILY === 'Windows' && ! $headless) {
            $batDir = storage_path('app/temp');
            if (! is_dir($batDir)) {
                mkdir($batDir, 0755, true);
            }
            $batPath = $batDir . DIRECTORY_SEPARATOR . 'envato-' . $descarga->id . '.bat';
            $outputFile = $batDir . DIRECTORY_SEPARATOR . 'envato-' . $descarga->id . '.out';
            $urlEscaped = str_replace(['%', '^', '&', '<', '>', '|', '"'], ['%%', '^^', '^&', '^<', '^>', '^|', '""'], $descarga->url);
            $batContent = "@echo off\r\n";
            $batContent .= 'cd /d "' . str_replace('"', '""', base_path()) . '"' . "\r\n";
            $batContent .= '"' . str_replace('"', '""', $nodeBinary) . '" "' . str_replace('"', '""', $scriptPath) . '" ';
            $batContent .= '--url="' . $urlEscaped . '" --id=' . $descarga->id . ' ';
            $batContent .= '--downloads="' . str_replace('"', '""', $downloadsPath) . '" ';
            $batContent .= '--auth="' . str_replace('"', '""', $authPath) . '" --headless=false ';
            $batContent .= '> "' . str_replace('"', '""', $outputFile) . '" 2>&1' . "\r\n";
            file_put_contents($batPath, $batContent);

            $process = new Process([
                'cmd', '/c', $batPath,
            ], base_path(), null, null, 300);

            Log::info('ProcessEnvatoDescarga: Windows con ventana', [
                'descarga_id' => $descarga->id,
                'bat' => $batPath,
            ]);
        } else {
            $args = [
                $nodeBinary,
                $scriptPath,
                '--url=' . $descarga->url,
                '--id=' . $descarga->id,
                '--downloads=' . $downloadsPath,
                '--auth=' . $authPath,
                '--headless=' . $headlessArg,
            ];
            $process = new Process($args, base_path(), null, null, 300);

            Log::info('ProcessEnvatoDescarga: iniciando', [
                'descarga_id' => $descarga->id,
                'headless' => $headless,
            ]);
        }

        try {
            Log::info('ProcessEnvatoDescarga: ejecutando proceso', [
                'descarga_id' => $descarga->id,
                'timeout_seconds' => 300,
                'output_file' => $outputFile,
            ]);
            $process->run();
            Log::info('ProcessEnvatoDescarga: proceso finalizado', [
                'descarga_id' => $descarga->id,
                'exit_code' => $process->getExitCode(),
                'successful' => $process->isSuccessful(),
                'output_len' => strlen($process->getOutput() ?? ''),
                'error_len' => strlen($process->getErrorOutput() ?? ''),
                'output_file' => $outputFile,
            ]);
        } catch (ProcessTimedOutException $e) {
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => 'El proceso agotó el tiempo de espera (5 min). Chromium puede bloquearse cuando el job corre desde la cola. Prueba ENVATO_HEADLESS=true.',
                'procesado_en' => now(),
            ]);
            Log::warning('ProcessEnvatoDescarga: timeout', ['descarga_id' => $descarga->id]);

            return;
        }

        if (! $process->isSuccessful()) {
            $errorDetail = trim($process->getErrorOutput() ?: $process->getOutput());
            if ($outputFile && file_exists($outputFile)) {
                $fileContent = trim(file_get_contents($outputFile));
                if ($fileContent !== '') {
                    $errorDetail = $fileContent;
                }
            }
            $descarga->update([
                'estado' => 'error',
                'error_detalle' => $errorDetail,
                'procesado_en' => now(),
            ]);

            Log::warning('Envato download failed', [
                'descarga_id' => $descarga->id,
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);

            if ($outputFile && file_exists($outputFile)) {
                @unlink($outputFile);
            }
            if (isset($batPath) && file_exists($batPath ?? '')) {
                @unlink($batPath);
            }

            return;
        }

        $processOutput = $outputFile && file_exists($outputFile)
            ? trim(file_get_contents($outputFile))
            : $process->getOutput();

        Log::info('ProcessEnvatoDescarga: salida recolectada', [
            'descarga_id' => $descarga->id,
            'output_file' => $outputFile,
            'output_preview' => mb_substr($processOutput ?? '', 0, 2000),
        ]);

        if ($outputFile && file_exists($outputFile)) {
            @unlink($outputFile);
        }
        if ($outputFile) {
            $batPath = dirname($outputFile) . DIRECTORY_SEPARATOR . 'envato-' . $descarga->id . '.bat';
            if (file_exists($batPath)) {
                @unlink($batPath);
            }
        }

        $result = $this->decodeResultWithLogs($processOutput);

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

            Log::warning('ProcessEnvatoDescarga: salida sin JSON valido', [
                'descarga_id' => $descarga->id,
                'output_preview' => mb_substr($processOutput ?? '', 0, 4000),
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

    private function decodeResultWithLogs(string $rawOutput): ?array
    {
        $rawOutput = trim($rawOutput);

        if ($rawOutput === '') {
            return null;
        }

        // Caso simple: salida pura JSON.
        $direct = json_decode($rawOutput, true);
        if (is_array($direct)) {
            return $direct;
        }

        // Caso con logs mezclados: buscar el ultimo JSON valido por linea.
        $lines = preg_split("/\r\n|\n|\r/", $rawOutput) ?: [];
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if ($line === '' || $line[0] !== '{') {
                continue;
            }

            $candidate = json_decode($line, true);
            if (is_array($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
