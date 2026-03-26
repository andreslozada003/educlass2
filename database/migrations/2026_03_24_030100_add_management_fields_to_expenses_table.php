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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('expense_categories')->nullOnDelete();
            $table->string('expense_type', 20)->default('variable')->after('description');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'cancelled', 'overdue'])->default('paid')->after('amount');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
            $table->string('payment_source')->nullable()->after('payment_method');
            $table->string('invoice_number')->nullable()->after('receipt_number');
            $table->date('due_date')->nullable()->after('expense_date');
            $table->date('paid_date')->nullable()->after('due_date');
            $table->string('branch_name')->nullable()->after('supplier_id');
            $table->foreignId('responsible_user_id')->nullable()->after('branch_name')->constrained('users')->nullOnDelete();
            $table->enum('approval_status', ['not_required', 'pending', 'approved', 'rejected'])->default('not_required')->after('responsible_user_id');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->date('next_due_date')->nullable()->after('recurring_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropConstrainedForeignId('responsible_user_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'expense_type',
                'payment_status',
                'paid_amount',
                'payment_source',
                'invoice_number',
                'due_date',
                'paid_date',
                'branch_name',
                'approval_status',
                'approved_at',
                'next_due_date',
            ]);
        });
    }
};
