<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    public function test_visitor_can_send_a_contact_message(): void
    {
        Mail::fake();

        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'phone' => '502 293 849',
            'message' => 'Proszę o informację o terminie realizacji.',
        ]);

        $response
            ->assertRedirect(route('contact'))
            ->assertSessionHas('contact_message_sent', true)
            ->assertSessionHasNoErrors();

        Mail::assertQueued(ContactMessageReceived::class, function (ContactMessageReceived $mail): bool {
            return $mail->name === 'Jan Kowalski'
                && $mail->email === 'jan@example.com'
                && $mail->phone === '502 293 849'
                && $mail->body === 'Proszę o informację o terminie realizacji.';
        });
    }

    public function test_contact_message_requires_valid_contact_details(): void
    {
        Mail::fake();

        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'name' => '',
            'email' => 'invalid',
            'message' => '',
        ]);

        $response
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors([
                'name' => 'Podaj imię i nazwisko.',
                'email' => 'Podaj prawidłowy adres email.',
                'message' => 'Napisz wiadomość.',
            ]);

        Mail::assertNotQueued(ContactMessageReceived::class);
    }

    public function test_contact_message_submission_is_rate_limited(): void
    {
        Mail::fake();
        $payload = [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'Proszę o informację o terminie realizacji.',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.1'])
                ->post(route('contact.store'), $payload)
                ->assertRedirect(route('contact'));
        }

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.1'])
            ->post(route('contact.store'), $payload)
            ->assertTooManyRequests();

        Mail::assertQueued(ContactMessageReceived::class, 5);
    }

    public function test_contact_message_email_escapes_visitor_content(): void
    {
        $mail = new ContactMessageReceived(
            name: 'Jan <script>alert("xss")</script>',
            email: 'jan@example.com',
            phone: null,
            body: '<script>alert("xss")</script>',
        );

        $html = $mail->render();

        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);
    }
}
