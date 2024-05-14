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
        Schema::create('tokens_de_acceso_personal', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->morphs('tokenizable');
            $tabla->string('nombre');
            $tabla->string('token', 64)->unique();
            $tabla->text('capacidades')->nullable();
            $tabla->timestamp('ultimo_uso_en')->nullable();
            $tabla->timestamp('expira_en')->nullable();
            $tabla->timestamps();  // Esto crea automáticamente los campos 'creado_en' y 'actualizado_en'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_de_acceso_personal');
    }
};
