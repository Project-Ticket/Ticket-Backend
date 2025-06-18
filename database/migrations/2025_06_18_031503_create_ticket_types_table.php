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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('quantity');
            $table->integer('sold_quantity')->default(0);
            $table->integer('min_purchase')->default(1);
            $table->integer('max_purchase')->default(10);
            $table->datetime('sale_start');
            $table->datetime('sale_end');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('benefits')->nullable(); // JSON array of benefits
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
