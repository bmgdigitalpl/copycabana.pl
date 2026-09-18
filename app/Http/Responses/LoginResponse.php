<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        /** @var Request $request */
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->intended(
            $user->hasVerifiedEmail() ? route('customer.dashboard') : route('verification.notice')
        );
    }
}
