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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'CREDIT_CARD', 'BCA', etc.
            $table->string('name'); // Display name
            $table->string('type'); // e.g., 'bank_transfer', 'credit_card', 'ewallet'
            $table->text('description')->nullable();
            $table->decimal('fee_percentage', 5, 2)->default(0); // Fee in percentage
            $table->decimal('fee_fixed', 10, 2)->default(0); // Fixed fee amount
            $table->decimal('minimum_fee', 10, 2)->default(0); // Minimum fee
            $table->decimal('maximum_fee', 10, 2)->nullable(); // Maximum fee (nullable for no limit)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Additional settings if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
