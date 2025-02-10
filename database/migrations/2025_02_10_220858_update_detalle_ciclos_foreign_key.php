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
        Schema::table('detalle_ciclos', function (Blueprint $table) {
            // Eliminar la clave foránea existente
            $table->dropForeign(['representante_id']);
            
            // Modificar la columna para que apunte a representatives
            $table->foreign('representante_id')
                  ->references('id')
                  ->on('representatives')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_ciclos', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropForeign(['representante_id']);
            
            // Restaurar la clave foránea original
            $table->foreign('representante_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
