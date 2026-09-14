<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereIn('slug', ['uslugi-dodatkowe', 'oprawa-prac'])
            ->update([
                'is_active' => false,
                'is_business_configurator' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Keep historical products inactive when rolling back the migration.
    }
};
