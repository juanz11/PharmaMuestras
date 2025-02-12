<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->dropColumn('numero_descargo');
        });
    }

    public function down()
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->string('numero_descargo')->nullable();
        });
    }
};
