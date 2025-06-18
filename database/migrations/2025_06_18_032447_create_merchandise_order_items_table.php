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
        Schema::create('merchandise_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchandise_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_id')->constrained()->onDelete('restrict');
            $table->foreignId('merchandise_variant_id')->nullable()->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->json('variant_details')->nullable(); // Snapshot of variant at time of order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchandise_order_items');
    }
};
