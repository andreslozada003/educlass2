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
        Schema::create('reparaciones', function (Blueprint $table) {
            $table->id();
            $table->string('orden')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Información del dispositivo
            $table->string('dispositivo_tipo');
            $table->string('dispositivo_marca');
            $table->string('dispositivo_modelo');
            $table->string('dispositivo_color')->nullable();
            $table->string('dispositivo_imei')->nullable();
            $table->string('dispositivo_serial')->nullable();
            $table->string('dispositivo_contrasena')->nullable();
            
            // Problema y diagnóstico
            $table->text('problema_reportado');
            $table->text('diagnostico')->nullable();
            $table->text('solucion')->nullable();
            
            // Estados
            $table->enum('estado', [
                'recibido',
                'en_diagnostico',
                'espera_repuesto',
                'en_reparacion',
                'reparado',
                'listo',
                'entregado',
                'cancelado'
            ])->default('recibido');
            
            // Precios
            $table->decimal('costo_estimado', 12, 2)->default(0);
            $table->decimal('costo_final', 12, 2)->default(0);
            $table->decimal('adelanto', 12, 2)->default(0);
            $table->decimal('ganancia', 12, 2)->default(0);
            
            // Fechas
            $table->dateTime('fecha_recepcion');
            $table->dateTime('fecha_estimada_entrega')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            
            // Notas y garantía
            $table->text('notas_tecnico')->nullable();
            $table->text('notas_cliente')->nullable();
            $table->string('garantia_dias')->default('30');
            $table->text('accesorios_incluidos')->nullable();
            $table->text('condiciones_previas')->nullable();
            
            // Fotos
            $table->string('foto_antes_1')->nullable();
            $table->string('foto_antes_2')->nullable();
            $table->string('foto_antes_3')->nullable();
            $table->string('foto_despues_1')->nullable();
            $table->string('foto_despues_2')->nullable();
            $table->string('foto_despues_3')->nullable();
            
            // Notificaciones
            $table->boolean('notificado_listo')->default(false);
            $table->dateTime('fecha_notificacion')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparaciones');
    }
};
