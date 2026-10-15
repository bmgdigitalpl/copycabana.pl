<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(protected string $status) {}

    public function toResponse($request): Response
    {
        /** @var Request $request */
        if ($request->wantsJson()) {
            return response()->json(['message' => trans($this->status)]);
        }

        if (Auth::check()) {
            return redirect()->route('dashboard')->with('status', trans($this->status));
        }

        return redirect()->route('login')->with('status', trans($this->status));
    }
}
