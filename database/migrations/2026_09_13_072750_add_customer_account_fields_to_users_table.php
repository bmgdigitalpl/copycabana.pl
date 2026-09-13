<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('client_id')->nullable()->unique()->after('role')->constrained()->nullOnDelete();
            $table->timestamp('disabled_at')->nullable()->after('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropUnique('users_client_id_unique');
            $table->dropColumn(['client_id', 'disabled_at']);
        });
    }
};
