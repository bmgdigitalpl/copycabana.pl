<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncConfiguratorCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_makes_the_thesis_page_work_on_a_database_without_the_configurator(): void
    {
        Storage::fake('public');

        $this->artisan('configurator:sync')->assertSuccessful();

        $printing = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail()->configuration['printing'];

        $this->assertCount(31, $printing['cover_colors']);
        $this->assertSame(20, $printing['covers']['custom']['price']);
        $this->assertNotNull(Product::query()->where('slug', 'druk')->firstOrFail()->configuration['printing']);
        Storage::disk('public')->assertExists('thesis-cover-photos/fiolet.jpg');
        Storage::disk('public')->assertExists('thesis-cover-textures/fiolet.jpg');
        $this->get(route('services.diploma'))->assertOk();
    }

    public function test_it_keeps_a_saved_configurator_unless_forced(): void
    {
        Storage::fake('public');
        $this->seed(ProductSeeder::class);
        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $product->update(['configuration' => [...($product->configuration ?? []), 'printing' => ['max_copies' => 3, 'cover_colors' => ['x' => ['label' => 'X', 'hex' => '#000000']]]]]);

        $this->artisan('configurator:sync')->assertSuccessful();
        $this->assertSame(3, $product->fresh()->configuration['printing']['max_copies']);

        $this->artisan('configurator:sync', ['--force' => true])->assertSuccessful();
        $this->assertSame(10, $product->fresh()->configuration['printing']['max_copies']);
    }
}
