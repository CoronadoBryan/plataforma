<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('descargas', function (Blueprint $table) {
            $table->string('archivo_local')->nullable()->after('estado');
            $table->text('error_detalle')->nullable()->after('archivo_local');
            $table->timestamp('procesado_en')->nullable()->after('error_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descargas', function (Blueprint $table) {
            $table->dropColumn(['archivo_local', 'error_detalle', 'procesado_en']);
        });
    }
};
