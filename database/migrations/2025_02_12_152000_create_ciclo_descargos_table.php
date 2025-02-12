<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ciclo_descargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->foreignId('representante_id')->constrained('representatives')->onDelete('cascade');
            $table->string('numero_descargo');
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['ciclo_id', 'representante_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ciclo_descargos');
    }
};
