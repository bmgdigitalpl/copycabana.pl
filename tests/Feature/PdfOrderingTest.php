<?php

namespace Tests\Feature;

use App\Mail\OrderReceivedMail;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payu.pos_id' => 'pdf-test-pos',
            'services.payu.client_id' => 'test-client',
            'services.payu.client_secret' => 'test-secret',
            'services.payu.second_key' => 'test-second-key',
        ]);
    }

    public function test_customer_can_receive_a_server_side_pdf_quote(): void
    {
        Storage::fake('local');
        $upload = $this->post('/api/v1/uploads', ['file' => $this->pdfFile()]);

        $this->postJson('/api/v1/pdf/quote', [
            'upload_token' => $upload->json('upload_token'),
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'finish' => 'folder',
            'copies' => 2,
            'shipping_method' => 'parcel',
        ])->assertOk()
            ->assertJsonPath('quote.pages', 1)
            ->assertJsonPath('quote.print_total', 0.4)
            ->assertJsonPath('quote.finish_total', 16)
            ->assertJsonPath('quote.total', 28.4);
    }

    public function test_customer_can_create_a_pdf_order_and_attach_the_private_file(): void
    {
        Storage::fake('local');
        Mail::fake();
        Notification::fake();
        Http::fake([
            '*/pl/standard/user/oauth/authorize' => Http::response(['access_token' => 'test-token']),
            '*/api/v2_1/orders' => Http::response([
                'status' => ['statusCode' => 'SUCCESS'],
                'redirectUri' => 'https://payu.test/pdf-pay',
                'orderId' => 'PAYU-PDF-ORDER',
            ], 302, ['Location' => 'https://payu.test/pdf-pay']),
        ]);
        $administrator = User::factory()->create(['role' => 'admin']);
        $this->seed(ProductSeeder::class);
        $upload = $this->post('/api/v1/uploads', ['file' => $this->pdfFile()]);

        $response = $this->postJson('/api/v1/pdf/orders', [
            'upload_token' => $upload->json('upload_token'),
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'color_mode' => 'color',
            'sided' => 'simplex',
            'finish' => 'staples',
            'copies' => 1,
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('order.status', 'payment_awaited')
            ->assertJsonPath('order.total', '4.50')
            ->assertJsonPath('payment_url', 'https://payu.test/pdf-pay');
        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('attached', $order->files()->firstOrFail()->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'subtotal' => 4.50]);
        Mail::assertQueued(OrderReceivedMail::class, 1);
        Notification::assertSentTo($administrator, AdminActivityNotification::class);
    }

    public function test_pdf_order_requires_complete_delivery_address(): void
    {
        Storage::fake('local');
        $upload = $this->post('/api/v1/uploads', ['file' => $this->pdfFile()]);

        $this->postJson('/api/v1/pdf/orders', [
            'upload_token' => $upload->json('upload_token'),
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'color_mode' => 'bw',
            'sided' => 'duplex',
            'finish' => 'none',
            'copies' => 1,
            'shipping_method' => 'courier',
            'privacy_policy_accepted' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('shipping_address.address');
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

        return UploadedFile::fake()->createWithContent('document.pdf', $content);
    }
}
