<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fullNameExpression = DB::getDriverName() === 'sqlite'
            ? "first_name || ' ' || last_name"
            : 'CONCAT(first_name, " ", last_name)';

        Schema::create('customers', function (Blueprint $table) use ($fullNameExpression) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name')->virtualAs($fullNameExpression);
            $table->string('document_type', 10)->default('CC');
            $table->string('document_number')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('document_number');
            $table->index('email');
            $table->index('phone');
            $table->index('is_active');
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
