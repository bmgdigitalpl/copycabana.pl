<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('options', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('input_type')->default('select');
            $table->string('pricing_model')->default('fixed');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('option_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->decimal('price_modifier', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['option_id', 'value']);
        });

        Schema::create('option_product', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['product_id', 'option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_product');
        Schema::dropIfExists('option_values');
        Schema::dropIfExists('options');
    }
};
