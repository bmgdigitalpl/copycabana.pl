<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\User;
use App\Notifications\AdminInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BrandedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_mails_carry_the_embedded_logo_and_brand_colours(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $user->notify(new AdminInvitation('token'));

        $html = $this->lastSentHtml();
        $this->assertStringContainsString(asset('images/logo.png'), $html);
        $this->assertStringContainsString('Wygeneruj hasło', $html);
        $this->assertStringContainsString('#063a60', strtolower($html));
        $this->assertStringContainsString('ul. Bankowa 11', $html);
        $this->assertStringContainsString('Jeśli przycisk „Wygeneruj hasło” nie działa', $html);
        $this->assertStringNotContainsString('having trouble', $html);
    }

    public function test_application_mailables_use_the_same_branded_layout(): void
    {
        Mail::to('biuro@copycabana.pl')->send(new ContactMessageReceived(
            name: 'Jan',
            email: 'jan@example.com',
            phone: null,
            body: "Pierwsza linia\nDruga linia",
        ));

        $html = $this->lastSentHtml();
        $this->assertStringContainsString(asset('images/logo.png'), $html);
        $this->assertStringContainsString('Nowa wiadomość z formularza kontaktowego', $html);
        $this->assertStringContainsString('Pierwsza linia', $html);
        $this->assertStringContainsString('Druga linia', $html);
    }

    private function lastSentHtml(): string
    {
        $messages = app('mailer')->getSymfonyTransport()->messages();

        return $messages->last()->getOriginalMessage()->getHtmlBody();
    }
}
