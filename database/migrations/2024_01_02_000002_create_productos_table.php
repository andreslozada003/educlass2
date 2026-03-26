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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('imei')->nullable();
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->string('unidad_medida')->default('pieza');
            $table->string('imagen_principal')->nullable();
            $table->json('imagenes_adicionales')->nullable();
            $table->text('especificaciones_tecnicas')->nullable();
            $table->string('proveedor')->nullable();
            $table->string('garantia')->nullable();
            $table->enum('tipo', ['celular', 'accesorio', 'repuesto', 'servicio'])->default('accesorio');
            $table->boolean('es_servicio')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
