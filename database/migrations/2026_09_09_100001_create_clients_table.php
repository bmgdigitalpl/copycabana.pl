<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('nip', 20)->nullable()->index();
            $table->json('billing_address')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('marketing_consent_at')->nullable();
            $table->string('privacy_policy_version')->nullable();
            $table->timestamp('privacy_policy_accepted_at')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->timestamp('anonymized_at')->nullable();
            $table->timestamp('retention_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
