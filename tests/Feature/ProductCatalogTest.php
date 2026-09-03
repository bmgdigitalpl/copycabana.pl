<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_product_catalog_is_available_through_the_api(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->getJson('/api/v1/products');

        $response
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('data.0.slug', 'wizytowki')
            ->assertJsonPath('data.0.name', 'Wizytówki');
    }

    public function test_catalog_can_be_filtered_by_category(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->getJson('/api/v1/products?category=foto');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.category', 'foto')
            ->assertJsonPath('data.1.category', 'foto');
    }

    public function test_inactive_products_are_not_exposed(): void
    {
        $this->seed(ProductSeeder::class);
        Product::query()->where('slug', 'wizytowki')->update(['is_active' => false]);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonMissing(['slug' => 'wizytowki']);
    }

    public function test_product_can_be_loaded_by_slug(): void
    {
        $this->seed(ProductSeeder::class);

        $this->getJson('/api/v1/products/oprawa-prac')
            ->assertOk()
            ->assertJsonPath('data.slug', 'oprawa-prac')
            ->assertJsonPath('data.calculator_type', 'fixed');
    }
}
