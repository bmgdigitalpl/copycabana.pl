<?php

namespace Tests\Unit;

use App\Models\OrderFile;
use App\Services\PdfPricingService;
use App\Services\ThesisPricingService;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MixedColorPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_thesis_mixed_color_pricing_uses_detected_color_pages_only(): void
    {
        $this->seed(ProductSeeder::class);
        $quote = app(ThesisPricingService::class)->calculate($this->orderFile(), [
            'color_mode' => 'mixed',
            'sided' => 'duplex',
            'binding' => 'hard',
            'cover' => 'none',
            'burn_cd' => false,
            'copies' => 2,
            'shipping_method' => 'pickup',
        ]);

        $this->assertSame(2, $quote['color_pages']);
        $this->assertSame(8, $quote['bw_pages']);
        $this->assertSame(5.2, $quote['print_total']);
        $this->assertSame(105.2, $quote['total']);
    }

    public function test_pdf_mixed_color_pricing_uses_detected_color_pages_only(): void
    {
        $this->seed(ProductSeeder::class);
        $quote = app(PdfPricingService::class)->calculate($this->orderFile(), [
            'color_mode' => 'mixed',
            'sided' => 'duplex',
            'finish' => 'staples',
            'copies' => 2,
            'shipping_method' => 'pickup',
        ]);

        $this->assertSame(2, $quote['color_pages']);
        $this->assertSame(8, $quote['bw_pages']);
        $this->assertSame(5.2, $quote['print_total']);
        $this->assertSame(13.2, $quote['total']);
    }

    private function orderFile(): OrderFile
    {
        return new OrderFile([
            'pages' => 10,
            'color_pages' => 2,
            'bw_pages' => 8,
        ]);
    }
}
