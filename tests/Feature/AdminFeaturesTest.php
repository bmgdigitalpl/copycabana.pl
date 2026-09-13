<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_option_with_prices(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post(route('admin.options.store'), [
            'name' => 'Papier',
            'code' => 'papier',
            'input_type' => 'select',
            'pricing_model' => 'fixed',
            'is_active' => 1,
            'values' => [
                ['label' => 'Matowy', 'value' => 'matowy', 'price_modifier' => 2.50],
                ['label' => 'Błyszczący', 'value' => 'blyszczacy', 'price_modifier' => 4],
            ],
        ])->assertRedirect(route('admin.options.index'));

        $this->assertDatabaseHas('options', ['code' => 'papier']);
        $this->assertDatabaseHas('option_values', ['value' => 'matowy', 'price_modifier' => 2.5]);
    }

    public function test_non_admin_cannot_access_the_admin_area(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }

    public function test_inactive_administrator_cannot_access_the_admin_area(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'disabled_at' => now()]);

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }

    public function test_active_staff_passkey_can_be_used_to_log_in(): void
    {
        $user = User::factory()->make(['role' => 'staff']);
        $passkey = (new Passkey)->setRelation('user', $user);

        $this->assertTrue(Passkeys::allowsLogin(request(), $passkey));
    }

    #[DataProvider('ineligiblePasskeyUsers')]
    public function test_passkey_login_rejects_inactive_or_unauthorized_users(array $attributes): void
    {
        $user = User::factory()->make($attributes);
        $passkey = (new Passkey)->setRelation('user', $user);

        $this->assertFalse(Passkeys::allowsLogin(request(), $passkey));
    }

    /** @return array<string, array{array<string, string>}> */
    public static function ineligiblePasskeyUsers(): array
    {
        return [
            'customer' => [['role' => 'customer']],
            'inactive administrator' => [['role' => 'admin', 'disabled_at' => '2026-09-13 12:00:00']],
            'inactive staff member' => [['role' => 'staff', 'disabled_at' => '2026-09-13 12:00:00']],
        ];
    }

    public function test_guests_are_redirected_to_the_admin_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_login_is_rate_limited(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertRedirect();
        }

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_staff_can_manage_orders_but_cannot_access_owner_data(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($user)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.clients.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.options.index'))->assertForbidden();
    }

    public function test_security_headers_are_added_to_application_responses(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_admin_overviews_and_option_form_render(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['name' => 'Wizytówki']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Pulpit');
        $this->actingAs($user)->get(route('admin.options.create'))->assertOk()->assertSee('Dodaj opcję');
        $this->actingAs($user)->get(route('admin.products.index'))->assertOk()->assertSee('Wizytówki');
        $this->actingAs($user)->get(route('admin.products.edit', $product))->assertOk()->assertSee('Edytuj zdjęcie produktu');
        $this->actingAs($user)->get(route('admin.privacy.index'))->assertOk()->assertSee('Wnioski RODO');
    }

    public function test_admin_can_set_a_product_image_from_the_public_catalog(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $this->actingAs($user)->put(route('admin.products.update', $product), [
            'catalog_image_path' => 'images/produkty/ulotki.png',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image_path' => 'images/produkty/ulotki.png',
        ]);
    }

    public function test_admin_can_upload_a_new_product_image_and_remove_the_old_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'admin']);
        $oldImagePath = 'product-images/old.png';
        Storage::disk('public')->put($oldImagePath, 'old image');
        $product = Product::factory()->create(['image_path' => $oldImagePath]);
        $image = UploadedFile::fake()->image('new-image.png');

        $this->actingAs($user)->put(route('admin.products.update', $product), [
            'image' => $image,
        ])->assertRedirect(route('admin.products.index'));

        $newImagePath = $product->fresh()->image_path;
        $this->assertIsString($newImagePath);
        $this->assertStringStartsWith('product-images/', $newImagePath);
        Storage::disk('public')->assertExists($newImagePath);
        Storage::disk('public')->assertMissing($oldImagePath);
    }

    public function test_product_image_update_rejects_a_path_outside_the_public_catalog(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['image_path' => 'images/produkty/ulotki.png']);

        $this->actingAs($user)->put(route('admin.products.update', $product), [
            'catalog_image_path' => 'images/other/unsafe.png',
        ])->assertInvalid(['catalog_image_path']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image_path' => 'images/produkty/ulotki.png',
        ]);
    }

    public function test_product_image_update_rejects_non_image_uploads(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['image_path' => 'images/produkty/ulotki.png']);
        $file = UploadedFile::fake()->create('not-an-image.pdf', 10, 'application/pdf');

        $this->actingAs($user)->put(route('admin.products.update', $product), [
            'image' => $file,
        ])->assertInvalid(['image']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image_path' => 'images/produkty/ulotki.png',
        ]);
    }

    public function test_client_export_and_anonymization_are_available_to_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create(['email' => 'person@example.com']);

        $this->actingAs($user)->get(route('admin.clients.export', $client))->assertDownload('client-'.$client->id.'-data.json');
        $this->actingAs($user)->post(route('admin.clients.anonymize', $client))->assertRedirect();
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'email' => 'anonimowy-'.$client->id.'@example.invalid']);
    }

    public function test_expired_client_retention_is_anonymized_by_the_scheduled_command(): void
    {
        $client = Client::factory()->create(['retention_until' => now()->subDay()]);

        Artisan::call('clients:anonymize-expired');

        $this->assertNotNull($client->fresh()->anonymized_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'retention_anonymized', 'auditable_id' => $client->id]);
    }

    public function test_admin_can_login_through_fortify(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_fortify_login_screen_uses_the_flux_auth_view(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Zaloguj się do panelu')
            ->assertDontSee('Panel administracyjny');
    }

    public function test_customer_cannot_login_through_the_admin_fortify_form(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertInvalid('email');

        $this->assertGuest();
    }

    public function test_admin_can_view_and_mark_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Notification::send(
            $user,
            new AdminActivityNotification('Nowe zamówienie', 'Zamówienie CC-100 wymaga obsługi.', 'orders', route('admin.orders.index')),
        );

        $notification = $user->fresh()->unreadNotifications->first();

        $this->actingAs($user)->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Nowe zamówienie');

        $this->actingAs($user)->put(route('admin.notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Notification::send($user, new AdminActivityNotification('Aktualność', 'Sprawdź panel.', 'general'));

        $this->actingAs($user)->put(route('admin.notifications.read-all'))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'read_at' => null,
        ]);
    }
}
