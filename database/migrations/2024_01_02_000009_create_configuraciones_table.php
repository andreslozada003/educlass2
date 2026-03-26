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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('grupo');
            $table->string('clave');
            $table->text('valor')->nullable();
            $table->string('tipo')->default('string'); // string, integer, boolean, json
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->unique(['grupo', 'clave']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
