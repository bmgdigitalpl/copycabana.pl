<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('success_token_hash', 64)->nullable()->unique()->after('number');
            $table->string('idempotency_key', 100)->nullable()->unique()->after('success_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['orders_success_token_hash_unique']);
            $table->dropUnique(['orders_idempotency_key_unique']);
            $table->dropColumn(['success_token_hash', 'idempotency_key']);
        });
    }
};
