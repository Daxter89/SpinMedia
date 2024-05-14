<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    Schema::create('trabajos', function (Blueprint $tabla) {
        $tabla->id();
        $tabla->string('cola')->index();
        $tabla->longText('carga');
        $tabla->unsignedTinyInteger('intentos');
        $tabla->unsignedInteger('reservado_en')->nullable();
        $tabla->unsignedInteger('disponible_en');
        $tabla->unsignedInteger('creado_en');
    });

    Schema::create('lotes_de_trabajos', function (Blueprint $tabla) {
        $tabla->string('id')->primary();
        $tabla->string('nombre');
        $tabla->integer('total_trabajos');
        $tabla->integer('trabajos_pendientes');
        $tabla->integer('trabajos_fallidos');
        $tabla->longText('ids_trabajos_fallidos');
        $tabla->mediumText('opciones')->nullable();
        $tabla->integer('cancelado_en')->nullable();
        $tabla->integer('creado_en');
        $tabla->integer('finalizado_en')->nullable();
    });

    Schema::create('trabajos_fallidos', function (Blueprint $tabla) {
        $tabla->id();
        $tabla->string('uuid')->unique();
        $tabla->text('conexion');
        $tabla->text('cola');
        $tabla->longText('carga');
        $tabla->longText('excepcion');
        $tabla->timestamp('fallido_en')->useCurrent();
    });


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajos');
        Schema::dropIfExists('lotes_de_trabajos');
        Schema::dropIfExists('trabajos_fallidos');
    }
};
