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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('razon_social')->nullable()->after('apellido');
            $table->string('regimen_fiscal', 10)->nullable()->after('rfc');
            $table->string('uso_cfdi', 10)->nullable()->after('regimen_fiscal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'razon_social',
                'regimen_fiscal',
                'uso_cfdi',
            ]);
        });
    }
};
