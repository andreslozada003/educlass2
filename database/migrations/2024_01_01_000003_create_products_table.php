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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->string('model')->nullable();
            $table->string('imei')->nullable();
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('sale_price', 12, 2);
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5);
            $table->integer('max_stock')->nullable();
            $table->string('unit', 20)->default('unidad');
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('has_warranty')->default(false);
            $table->integer('warranty_days')->default(0);
            $table->boolean('is_service')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index('barcode');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('is_active');
            $table->index('is_service');
            $table->index('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
