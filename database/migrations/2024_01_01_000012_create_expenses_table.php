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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'check', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->date('expense_date');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('expense_number');
            $table->index('user_id');
            $table->index('category_id');
            $table->index('expense_date');
            $table->index('supplier_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
