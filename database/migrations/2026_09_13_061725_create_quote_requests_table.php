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
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->string('status', 30)->default('submitted')->index();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_email')->index();
            $table->string('customer_phone', 40)->nullable();
            $table->string('company_name', 150)->nullable();
            $table->string('nip', 20)->nullable();
            $table->string('shipping_method', 20)->nullable();
            $table->json('shipping_address')->nullable();
            $table->date('requested_by_date')->nullable();
            $table->boolean('invoice_required')->default(false);
            $table->string('privacy_policy_version', 30)->nullable();
            $table->timestamp('privacy_policy_accepted_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
