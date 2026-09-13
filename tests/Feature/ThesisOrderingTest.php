<?php

namespace Tests\Feature;

use App\Mail\OrderReceivedMail;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThesisOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payu.pos_id' => 'test-pos',
            'services.payu.client_id' => 'test-client',
            'services.payu.client_secret' => 'test-secret',
            'services.payu.second_key' => 'test-second-key',
        ]);

        Http::fake([
            '*/pl/standard/user/oauth/authorize' => Http::response(['access_token' => 'test-token']),
            '*/api/v2_1/orders' => Http::response([
                'status' => ['statusCode' => 'SUCCESS'],
                'redirectUri' => 'https://payu.test/pay',
                'orderId' => 'PAYU-THESIS-ORDER',
            ], 302, ['Location' => 'https://payu.test/pay']),
        ]);
    }

    public function test_customer_can_upload_pdf_and_receive_server_quote(): void
    {
        Storage::fake('local');

        $response = $this->post('/api/v1/uploads', [
            'file' => $this->pdfFile(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('file.pages', 1)
            ->assertJsonPath('file.name', 'thesis.pdf');
        $token = $response->json('upload_token');

        $this->postJson('/api/v1/thesis/quote', [
            'upload_token' => $token,
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'binding' => 'hard',
            'cover' => 'none',
            'copies' => 1,
            'shipping_method' => 'pickup',
        ])->assertOk()
            ->assertJsonPath('quote.pages', 1)
            ->assertJsonPath('quote.print_total', 0.2)
            ->assertJsonPath('quote.binding_total', 50)
            ->assertJsonPath('quote.total', 50.2);
    }

    public function test_customer_can_create_a_thesis_order_with_a_private_file(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(ProductSeeder::class);
        $upload = $this->post('/api/v1/uploads', ['file' => $this->pdfFile()]);

        $response = $this->postJson('/api/v1/thesis/orders', [
            'upload_token' => $upload->json('upload_token'),
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'binding' => 'hard',
            'cover' => 'none',
            'copies' => 1,
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
            'total' => 0,
        ]);

        $response->assertCreated()
            ->assertJsonPath('order.status', 'payment_awaited')
            ->assertJsonPath('order.total', '50.20')
            ->assertJsonPath('payment_url', 'https://payu.test/pay');

        $order = Order::query()->latest('id')->firstOrFail();
        $file = $order->files()->firstOrFail();
        $this->assertSame('attached', $file->status);
        $this->assertSame('thesis.pdf', $file->original_name);
        $this->assertSame(1, $file->pages);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'amount' => 50.20]);
        Mail::assertQueued(OrderReceivedMail::class);
        Storage::disk('local')->assertExists($file->path);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.orders.files.download', [$order, $file]))
            ->assertDownload($file->original_name);

        $this->postJson('/api/v1/thesis/quote', [
            'upload_token' => $upload->json('upload_token'),
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'binding' => 'hard',
            'cover' => 'none',
            'copies' => 1,
            'shipping_method' => 'pickup',
        ])->assertUnprocessable()->assertJsonValidationErrors('upload_token');
    }

    public function test_repeated_thesis_submission_with_the_same_idempotency_key_does_not_duplicate_the_order(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(ProductSeeder::class);
        $upload = $this->post('/api/v1/uploads', ['file' => $this->pdfFile()]);
        $payload = [
            'upload_token' => $upload->json('upload_token'),
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'binding' => 'hard',
            'cover' => 'none',
            'copies' => 1,
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ];

        $first = $this->withHeader('Idempotency-Key', 'thesis-idempotency-key')
            ->postJson('/api/v1/thesis/orders', $payload);
        $second = $this->withHeader('Idempotency-Key', 'thesis-idempotency-key')
            ->postJson('/api/v1/thesis/orders', $payload);

        $first->assertCreated();
        $second->assertOk();
        $this->assertSame($first->json('order.id'), $second->json('order.id'));
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_upload_rejects_a_non_pdf_file(): void
    {
        Storage::fake('local');

        $this->post('/api/v1/uploads', [
            'file' => UploadedFile::fake()->createWithContent('thesis.pdf', 'not a PDF'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_parcel_delivery_requires_a_selected_point(): void
    {
        Storage::fake('local');
        $this->seed(ProductSeeder::class);
        $upload = $this->post('/api/v1/uploads', ['file' => $this->pdfFile()]);

        $this->postJson('/api/v1/thesis/orders', [
            'upload_token' => $upload->json('upload_token'),
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'binding' => 'hard',
            'cover' => 'none',
            'copies' => 1,
            'shipping_method' => 'parcel',
            'privacy_policy_accepted' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('shipping_address.point_code');
    }

    private function pdfFile(): UploadedFile
    {
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << >> >>\nendobj\n",
            "4 0 obj\n<< /Length 0 >>\nstream\n\nendstream\nendobj\n",
        ];
        $content = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($content);
            $content .= $object;
        }

        $content .= "xref\n0 5\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $content .= sprintf("%010d 00000 n \n", $offset);
        }
        $content .= "trailer\n<< /Root 1 0 R /Size 5 >>\nstartxref\n".strrpos($content, 'xref')."\n%%EOF\n";

        return UploadedFile::fake()->createWithContent('thesis.pdf', $content);
    }
}
