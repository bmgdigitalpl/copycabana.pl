<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The custom cover text costs more than the standard preset (20 zł vs 15 zł),
     * since it takes more work than picking one of the standard university titles.
     */
    public function up(): void
    {
        $this->updateCustomCoverPrice(20);
    }

    public function down(): void
    {
        $this->updateCustomCoverPrice(10);
    }

    private function updateCustomCoverPrice(int $price): void
    {
        DB::table('products')
            ->where('slug', 'praca-dyplomowa')
            ->get(['id', 'configuration'])
            ->each(function (object $product) use ($price): void {
                $configuration = json_decode((string) $product->configuration, true) ?? [];

                if (! isset($configuration['printing']['covers']['custom'])) {
                    return;
                }

                $configuration['printing']['covers']['custom']['price'] = $price;

                DB::table('products')->where('id', $product->id)->update([
                    'configuration' => json_encode($configuration),
                    'updated_at' => now(),
                ]);
            });
    }
};
