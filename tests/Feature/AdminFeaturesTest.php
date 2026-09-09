<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_overviews_and_option_form_render(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->assertSee('Pulpit');
        $this->actingAs($user)->get(route('admin.options.create'))->assertOk()->assertSee('Dodaj opcję');
        $this->actingAs($user)->get(route('admin.privacy.index'))->assertOk()->assertSee('Wnioski RODO');
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
}
