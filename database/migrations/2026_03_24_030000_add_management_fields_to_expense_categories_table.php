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
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('icon')->constrained('expense_categories')->nullOnDelete();
            $table->string('expense_group')->nullable()->after('parent_id');
            $table->boolean('requires_approval')->default(false)->after('expense_group');
            $table->decimal('monthly_budget', 12, 2)->nullable()->after('requires_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'expense_group',
                'requires_approval',
                'monthly_budget',
            ]);
        });
    }
};
