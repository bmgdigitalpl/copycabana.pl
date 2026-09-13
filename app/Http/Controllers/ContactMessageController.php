<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        Mail::to(config('business.quote_email'))->queue(new ContactMessageReceived(
            name: $request->string('name')->trim()->toString(),
            email: $request->string('email')->trim()->toString(),
            phone: $request->filled('phone') ? $request->string('phone')->trim()->toString() : null,
            body: $request->string('message')->trim()->toString(),
        ));

        return to_route('contact')->with('contact_message_sent', true);
    }
}
