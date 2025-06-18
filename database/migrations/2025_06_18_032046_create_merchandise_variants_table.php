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
        Schema::create('merchandise_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchandise_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Size S - Red"
            $table->json('attributes'); // {"size": "S", "color": "Red"}
            $table->string('sku')->unique();
            $table->decimal('price_adjustment', 12, 2)->default(0); // + or - from base price
            $table->integer('stock_quantity')->default(0);
            $table->integer('sold_quantity')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchandise_variants');
    }
};
