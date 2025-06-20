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
        Schema::create('event_organizers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('organization_name');
            $table->string('organization_slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->string('contact_person');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')
                ->comment('Status verifikasi legalitas dan dokumen EO oleh admin');;
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('application_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending')
                ->comment('Status pengajuan EO untuk aktif menjadi penyelenggara event');;
            $table->decimal('application_fee', 12, 2)->nullable();
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->json('required_documents')->nullable();
            $table->json('uploaded_documents')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->datetime('application_submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->datetime('reviewed_at')->nullable();
            $table->integer('status')->default(1)
                ->comment('Status aktif/nonaktif data EO, 1 = aktif, 0 = nonaktif');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_organizers');
    }
};
