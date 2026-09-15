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
        Schema::table('order_files', function (Blueprint $table) {
            $table->unsignedInteger('color_pages')->default(0)->after('pages');
            $table->unsignedInteger('bw_pages')->default(0)->after('color_pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn(['color_pages', 'bw_pages']);
        });
    }
};
