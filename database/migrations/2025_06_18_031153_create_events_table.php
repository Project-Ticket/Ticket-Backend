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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('event_organizers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('terms_conditions')->nullable();
            $table->string('banner_image');
            $table->json('gallery_images')->nullable();
            $table->enum('type', ['online', 'offline', 'hybrid'])->default('offline');
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_province')->nullable();
            $table->decimal('venue_latitude', 10, 8)->nullable();
            $table->decimal('venue_longitude', 11, 8)->nullable();
            $table->string('online_platform')->nullable();
            $table->text('online_link')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->datetime('registration_start');
            $table->datetime('registration_end');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('status')->default(1);
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'start_datetime']);
            $table->index(['category_id', 'status']);
            $table->index(['organizer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
