<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('carrier')->nullable()->after('shipping_method');
            $table->string('tracking_number')->nullable()->after('carrier');
            $table->decimal('tax_rate', 5, 2)->default(23)->after('shipping_total');
            $table->boolean('invoice_required')->default(false)->after('tax_rate');
            $table->string('invoice_nip', 20)->nullable()->after('invoice_required');
            $table->timestamp('delivered_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'client_id',
                'carrier',
                'tracking_number',
                'tax_rate',
                'invoice_required',
                'invoice_nip',
                'delivered_at',
            ]);
        });
    }
};
