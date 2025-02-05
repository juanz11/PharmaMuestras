<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estado')->default('En progreso');
            $table->decimal('porcentaje_hospitalario', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('detalle_ciclos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained()->onDelete('cascade');
            $table->foreignId('representante_id')->constrained('users');
            $table->foreignId('especialidad_id')->constrained('medical_specialties');
            $table->foreignId('producto_id')->constrained('products');
            $table->integer('cantidad_por_doctor');
            $table->integer('cantidad_total');
            $table->integer('cantidad_con_porcentaje');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_ciclos');
        Schema::dropIfExists('ciclos');
    }
};
