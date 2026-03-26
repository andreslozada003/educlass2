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
        Schema::create('reparacion_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reparacion_id')->constrained('reparaciones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('estado_anterior', [
                'recibido',
                'en_diagnostico',
                'espera_repuesto',
                'en_reparacion',
                'reparado',
                'listo',
                'entregado',
                'cancelado'
            ]);
            $table->enum('estado_nuevo', [
                'recibido',
                'en_diagnostico',
                'espera_repuesto',
                'en_reparacion',
                'reparado',
                'listo',
                'entregado',
                'cancelado'
            ]);
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparacion_historial');
    }
};
