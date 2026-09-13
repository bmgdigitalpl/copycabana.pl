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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('idempotency_fingerprint', 64)->nullable()->after('idempotency_key');
        });

        Schema::table('quote_requests', function (Blueprint $table): void {
            $table->string('idempotency_fingerprint', 64)->nullable()->after('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('idempotency_fingerprint');
        });

        Schema::table('quote_requests', function (Blueprint $table): void {
            $table->dropColumn('idempotency_fingerprint');
        });
    }
};
