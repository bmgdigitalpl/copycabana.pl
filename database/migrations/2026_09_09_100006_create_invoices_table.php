<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_company')->nullable();
            $table->string('buyer_nip', 20)->nullable();
            $table->json('buyer_address')->nullable();
            $table->char('currency', 3)->default('PLN');
            $table->decimal('net_total', 12, 2);
            $table->decimal('vat_total', 12, 2);
            $table->decimal('gross_total', 12, 2);
            $table->decimal('vat_rate', 5, 2)->default(23);
            $table->string('status')->default('issued');
            $table->timestamp('issued_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
