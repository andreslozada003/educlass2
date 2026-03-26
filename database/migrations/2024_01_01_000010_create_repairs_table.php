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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('repair_code')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Información del dispositivo
            $table->enum('device_type', ['iphone', 'android', 'tablet', 'other']);
            $table->string('brand');
            $table->string('model');
            $table->string('imei')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('color')->nullable();
            $table->string('storage')->nullable();
            
            // Detalles de la reparación
            $table->text('reported_issue');
            $table->text('diagnosis')->nullable();
            $table->text('solution')->nullable();
            $table->enum('status', ['received', 'diagnosing', 'waiting_parts', 'in_repair', 'repaired', 'ready', 'delivered', 'cancelled'])->default('received');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Costos
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('parts_cost', 12, 2)->default(0);
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('advance_payment', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            
            // Garantía
            $table->boolean('has_warranty')->default(false);
            $table->integer('warranty_days')->default(30);
            $table->date('warranty_starts_at')->nullable();
            $table->date('warranty_expires_at')->nullable();
            $table->string('warranty_code')->nullable();
            
            // Fechas
            $table->timestamp('received_at');
            $table->timestamp('diagnosed_at')->nullable();
            $table->timestamp('repaired_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            
            // Condiciones del dispositivo
            $table->json('device_condition')->nullable();
            $table->text('accessories_received')->nullable();
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('repair_code');
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('technician_id');
            $table->index('status');
            $table->index('priority');
            $table->index('device_type');
            $table->index('imei');
            $table->index('created_at');
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
