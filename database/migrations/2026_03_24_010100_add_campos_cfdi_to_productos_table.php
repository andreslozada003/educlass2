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
        Schema::table('productos', function (Blueprint $table) {
            $table->string('clave_prod_serv_sat', 20)->default('01010101')->after('unidad_medida');
            $table->string('clave_unidad_sat', 10)->default('H87')->after('clave_prod_serv_sat');
            $table->string('unidad_sat', 50)->default('Pieza')->after('clave_unidad_sat');
            $table->string('objeto_impuesto', 5)->default('02')->after('unidad_sat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'clave_prod_serv_sat',
                'clave_unidad_sat',
                'unidad_sat',
                'objeto_impuesto',
            ]);
        });
    }
};
