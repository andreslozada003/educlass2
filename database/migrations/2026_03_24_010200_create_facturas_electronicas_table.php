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
        Schema::create('facturas_electronicas', function (Blueprint $table) {
            $table->id();
            $table->string('folio_interno')->unique();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['borrador', 'lista_para_timbrar', 'timbrada', 'error', 'cancelada'])->default('borrador');
            $table->string('cfdi_version', 10)->default('4.0');
            $table->string('tipo_comprobante', 5)->default('I');
            $table->string('serie', 20)->nullable();
            $table->string('folio', 50)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->string('forma_pago', 5)->nullable();
            $table->string('metodo_pago_sat', 5)->nullable();
            $table->string('uso_cfdi', 10)->nullable();
            $table->string('exportacion', 5)->default('01');
            $table->string('lugar_expedicion', 10)->nullable();
            $table->string('regimen_fiscal_emisor', 10)->nullable();
            $table->string('regimen_fiscal_receptor', 10)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('pac_driver', 100)->nullable();
            $table->string('pac_modo', 20)->nullable();
            $table->string('uuid')->nullable()->unique();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('acuse_cancelacion_path')->nullable();
            $table->dateTime('fecha_timbrado')->nullable();
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->unsignedInteger('intentos_timbrado')->default(0);
            $table->text('error_mensaje')->nullable();
            $table->json('emisor_datos')->nullable();
            $table->json('receptor_datos')->nullable();
            $table->json('conceptos')->nullable();
            $table->json('payload_preparado')->nullable();
            $table->json('respuesta_pac')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_electronicas');
    }
};
