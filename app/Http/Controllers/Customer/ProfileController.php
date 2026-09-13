<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeCustomerPasswordRequest;
use App\Http\Requests\UpdateCustomerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('customer.profile.edit', ['client' => $request->user()->client]);
    }

    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $request->user()->client->update($data);
        $request->user()->forceFill(['name' => $data['name']])->save();

        return back()->with('status', 'Dane konta zostały zapisane.');
    }

    public function password(ChangeCustomerPasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill(['password' => $request->validated('password')])->save();

        return back()->with('status', 'Hasło zostało zmienione.');
    }
}
