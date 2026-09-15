<?php

namespace Tests\Feature;

use App\Mail\B2bQuoteRequestConfirmation;
use App\Models\OrderFile;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConfiguratorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_thesis_options_and_prices_used_by_the_public_quote(): void
    {
        $this->seed(ProductSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $payload = $this->printingPayload($product);
        $payload['bindings'] = [['key' => 'premium', 'label' => 'Oprawa premium', 'price' => 70, 'hint' => 'Nowa oprawa']];
        $payload['page_prices'] = ['bw' => 0.30, 'color' => 0.80];
        $payload['cd']['price'] = 12;
        $payload['spine_engraving']['price'] = 9;
        $payload['max_copies'] = 3;

        $this->actingAs($admin)->put(route('admin.printing.update', 'thesis'), $payload)
            ->assertRedirect(route('admin.printing.edit', 'thesis'))->assertSessionHasNoErrors();

        $this->assertSame('Oprawa premium', $product->fresh()->configuration['printing']['bindings']['premium']['label']);
        $this->get(route('services.diploma'))->assertSee('Oprawa premium')->assertSee('70,00 zł')->assertDontSee('value="hard"', false);
        $quote = $this->thesisQuotePayload();
        $quote['binding'] = 'premium';
        $quote['copies'] = 2;
        $quote['burn_cd'] = true;
        $quote['spine_engraving'] = true;
        $quote['spine_engraving_name'] = 'Jan Kowalski';
        $quote['total'] = 0;

        $this->postJson(route('api.thesis.quote'), $quote)->assertOk()
            ->assertJsonPath('quote.print_total', 8)
            ->assertJsonPath('quote.binding_total', 140)
            ->assertJsonPath('quote.spine_engraving_total', 18)
            ->assertJsonPath('quote.cd_total', 12)
            ->assertJsonPath('quote.total', 178);

        $quote['binding'] = 'hard';
        $this->postJson(route('api.thesis.quote'), $quote)->assertUnprocessable()->assertJsonValidationErrors('binding');
        $quote['binding'] = 'premium';
        $quote['copies'] = 4;
        $this->postJson(route('api.thesis.quote'), $quote)->assertUnprocessable()->assertJsonValidationErrors('copies');
    }

    public function test_admin_product_changes_reach_b2b_catalog_and_price_snapshot(): void
    {
        Mail::fake();
        $product = Product::factory()->create(['slug' => 'wizytowki', 'is_business_configurator' => true]);
        $admin = User::factory()->create(['role' => 'admin']);
        $fields = [[
            'key' => 'paper', 'label' => 'Papier specjalny', 'type' => 'chips', 'required' => true,
            'values' => [['value' => 'linen', 'label' => 'Papier lniany', 'price' => 12.50]],
        ]];

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Wizytówki premium', 'description' => 'Druk na papierze lnianym',
            'starting_price' => 45, 'sort_order' => 2, 'is_active' => true, 'fields' => $fields,
            'catalog_image_path' => 'images/produkty/ulotki.png',
        ])->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();

        $this->get(route('services.business'))->assertViewHas('b2bCatalog', fn (array $catalog): bool => $catalog[0]['name'] === 'Wizytówki premium' && $catalog[0]['params'][0]['values'][0]['price'] === 12.5);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'starting_price' => 45, 'image_path' => 'images/produkty/ulotki.png']);
        $this->withHeader('Idempotency-Key', 'configured-price')->postJson(route('api.b2b.quote-requests.store'), [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com', 'company' => 'Firma'],
            'items' => [['product_slug' => 'wizytowki', 'quantity' => 5, 'configuration' => ['paper' => 'linen']]],
            'shipping_method' => 'pickup', 'privacy_policy_accepted' => true,
        ])->assertCreated();
        $item = QuoteRequest::query()->firstOrFail()->items()->firstOrFail();
        $this->assertSame(12.5, $item->configuration['paper']['price']);
        $this->assertSame('Papier lniany', $item->configuration['paper']['display']);
        $fields[0]['values'][0]['price'] = 99;
        $this->put(route('admin.products.update', $product), ['fields' => $fields])->assertSessionHasNoErrors();
        $this->assertSame(12.5, $item->fresh()->configuration['paper']['price']);
        $this->get(route('admin.quote-requests.show', $item->quote_request_id))->assertSee('12,50 zł');
        Mail::assertQueued(B2bQuoteRequestConfirmation::class);
    }

    public function test_pdf_prices_and_new_finishes_can_be_managed_in_products(): void
    {
        $this->seed(ProductSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::query()->where('slug', 'druk')->firstOrFail();
        $payload = $this->printingPayload($product);
        $payload['finishes'] = [['key' => 'spiral', 'label' => 'Spirala', 'price' => 6]];
        $payload['page_prices']['bw'] = 0.40;
        $this->actingAs($admin)->put(route('admin.printing.update', 'pdf'), $payload)->assertSessionHasNoErrors();
        $quote = $this->thesisQuotePayload();

        $this->postJson(route('api.pdf.quote'), [
            'upload_token' => $quote['upload_token'], 'color_mode' => 'bw', 'sided' => 'duplex',
            'finish' => 'spiral', 'copies' => 2, 'shipping_method' => 'pickup',
        ])->assertOk()->assertJsonPath('quote.total', 20);
    }

    public function test_repeat_seeding_preserves_admin_configuration_and_images(): void
    {
        $this->seed(ProductSeeder::class);
        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $configuration = $product->configuration;
        $configuration['printing']['bindings']['hard']['price'] = 123;
        unset($configuration['printing']['universities']['us']);
        $product->update(['configuration' => $configuration, 'image_path' => 'product-images/custom.png', 'is_active' => false]);

        $this->seed(ProductSeeder::class);

        $this->assertSame($configuration, $product->fresh()->configuration);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'image_path' => 'product-images/custom.png', 'is_active' => false]);
        $this->assertDatabaseCount('products', 19);
    }

    public function test_guests_and_staff_cannot_change_configurators(): void
    {
        $product = Product::factory()->create();
        $this->put(route('admin.products.update', $product), [])->assertRedirect(route('login'));
        $this->put(route('admin.printing.update', 'thesis'), [])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'staff']));
        $this->put(route('admin.products.update', $product), [])->assertForbidden();
        $this->put(route('admin.printing.update', 'thesis'), [])->assertForbidden();
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_product_options_are_rejected_without_changing_the_product(array $fields): void
    {
        $product = Product::factory()->create(['configuration' => ['existing' => true]]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.products.update', $product), ['fields' => $fields])->assertInvalid();

        $this->assertSame(['existing' => true], $product->fresh()->configuration);
    }

    public static function invalidFields(): array
    {
        $field = ['key' => 'paper', 'label' => 'Papier', 'type' => 'chips', 'required' => true];

        return [
            'empty variants' => [[$field + ['values' => []]]],
            'duplicate fields' => [[$field, $field]],
            'negative price' => [[$field + ['values' => [['value' => 'mat', 'label' => 'Mat', 'price' => -1]]]]],
            'duplicate values' => [[$field + ['values' => [['value' => 'mat', 'label' => 'Mat', 'price' => 1], ['value' => 'mat', 'label' => 'Inny', 'price' => 2]]]]],
            'invalid range' => [[['key' => 'qty', 'label' => 'Nakład', 'type' => 'number', 'required' => true, 'min' => 5, 'max' => 1]]],
        ];
    }

    public function test_printing_editor_rejects_negative_prices_duplicate_codes_and_unsafe_colors(): void
    {
        $this->seed(ProductSeeder::class);
        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $before = $product->configuration;
        $payload = $this->printingPayload($product);
        $payload['bindings'][0]['price'] = -1;
        $payload['universities'][1]['key'] = $payload['universities'][0]['key'];
        $payload['imprint_colors'][0]['hex'] = 'red;background:url(unsafe)';

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.printing.update', 'thesis'), $payload)
            ->assertInvalid(['bindings.0.price', 'universities.1.key', 'imprint_colors.0.hex']);

        $this->assertSame($before, $product->fresh()->configuration);
    }

    public function test_editors_render_existing_options_and_escape_names(): void
    {
        $this->seed(ProductSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.printing.edit', 'thesis'))->assertSee('Prace Dyplomowe')->assertSee('Kolory nadruku');
        $this->get(route('admin.printing.edit', 'pdf'))->assertSee('Wykończenia');
        $product = Product::query()->where('slug', 'wizytowki')->firstOrFail();
        $product->update(['name' => '<script>alert(1)</script>']);
        $this->get(route('admin.products.edit', $product))->assertSee('Opcje konfiguratora B2B')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_inactive_thesis_product_cannot_be_quoted_or_opened(): void
    {
        $this->seed(ProductSeeder::class);
        Product::query()->where('slug', 'praca-dyplomowa')->update(['is_active' => false]);

        $this->get(route('services.diploma'))->assertNotFound();
        $this->postJson(route('api.thesis.quote'), $this->thesisQuotePayload())
            ->assertUnprocessable()->assertJsonValidationErrors('configuration');
    }

    /** @return array<string, mixed> */
    private function printingPayload(Product $product): array
    {
        $printing = $product->configuration['printing'];
        foreach (['bindings', 'covers', 'finishes', 'universities', 'cover_titles', 'imprint_colors', 'cover_colors'] as $group) {
            if (isset($printing[$group])) {
                $printing[$group] = collect($printing[$group])->map(fn (mixed $value, string $key): array => [
                    'key' => $key, ...(is_array($value) ? $value : ['label' => $value]),
                ])->values()->all();
            }
        }

        return $printing;
    }

    /** @return array<string, mixed> */
    private function thesisQuotePayload(): array
    {
        $token = str_repeat('a', 64);
        OrderFile::create([
            'token_hash' => hash('sha256', $token), 'disk' => 'local', 'path' => 'tests/thesis.pdf',
            'original_name' => 'thesis.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
            'sha256' => str_repeat('b', 64), 'pages' => 10, 'bw_pages' => 8, 'color_pages' => 2,
            'status' => 'temporary',
        ]);

        return [
            'upload_token' => $token, 'color_mode' => 'mixed', 'sided' => 'duplex', 'binding' => 'hard',
            'cover' => 'none', 'burn_cd' => false, 'copies' => 1, 'shipping_method' => 'pickup',
        ];
    }
}
