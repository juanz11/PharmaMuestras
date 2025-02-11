<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todos los productos que tienen una especialidad médica asignada
        $products = DB::table('products')
            ->whereNotNull('medical_specialty_id')
            ->get();

        // Insertar los registros en la tabla pivot
        foreach ($products as $product) {
            DB::table('medical_specialty_product')->insert([
                'product_id' => $product->id,
                'medical_specialty_id' => $product->medical_specialty_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Eliminar la columna medical_specialty_id de la tabla products
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['medical_specialty_id']);
            $table->dropColumn('medical_specialty_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Agregar la columna medical_specialty_id de vuelta a la tabla products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('medical_specialty_id')->nullable()->constrained()->onDelete('set null');
        });

        // Obtener los datos de la tabla pivot
        $relations = DB::table('medical_specialty_product')->get();

        // Mover los datos de vuelta a la columna medical_specialty_id
        foreach ($relations as $relation) {
            DB::table('products')
                ->where('id', $relation->product_id)
                ->update(['medical_specialty_id' => $relation->medical_specialty_id]);
        }
    }
};
