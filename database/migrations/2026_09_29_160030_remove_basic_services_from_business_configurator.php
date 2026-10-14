<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereIn('slug', ['projektowanie', 'druk', 'ksero', 'skanowanie'])
            ->update([
                'is_business_configurator' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->whereIn('slug', ['projektowanie', 'druk', 'ksero', 'skanowanie'])
            ->update([
                'is_business_configurator' => true,
                'updated_at' => now(),
            ]);
    }
};
