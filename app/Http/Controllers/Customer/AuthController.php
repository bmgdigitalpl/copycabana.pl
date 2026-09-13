<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCustomerRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function createLogin(): View
    {
        return view('customer.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $credentials['email'] = Str::lower($credentials['email']);
        $credentials['role'] = 'customer';
        $credentials['disabled_at'] = null;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Nieprawidłowy adres e-mail lub hasło.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('customer.dashboard'))
            : redirect()->route('verification.notice');
    }

    public function createRegister(): View
    {
        return view('customer.auth.register');
    }

    public function register(RegisterCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $email = Str::lower($data['email']);

        $user = DB::transaction(function () use ($data, $email): User {
            $client = Client::query()->firstOrNew(['email' => $email]);
            $client->fill([
                'name' => $data['name'],
                'privacy_policy_version' => config('privacy.policy_version'),
                'privacy_policy_accepted_at' => now(),
                'retention_until' => now()->addDays((int) config('privacy.client_retention_days')),
            ]);
            $client->save();

            return User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => $data['password'],
                'role' => 'customer',
                'client_id' => $client->id,
            ]);
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function forgotPassword(): View
    {
        return view('customer.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = Str::lower($data['email']);

        if (User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->whereNotNull('client_id')
            ->whereNull('disabled_at')
            ->exists()) {
            Password::broker()->sendResetLink(['email' => $email]);
        }

        return back()->with('status', 'Jeśli konto istnieje, wysłaliśmy instrukcję zmiany hasła.');
    }

    public function resetPasswordForm(string $token): View
    {
        return view('customer.auth.reset-password', [
            'token' => $token,
            'email' => request()->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);
        $data['email'] = Str::lower($data['email']);

        $customerExists = User::query()
            ->where('email', $data['email'])
            ->where('role', 'customer')
            ->whereNotNull('client_id')
            ->whereNull('disabled_at')
            ->exists();

        if (! $customerExists) {
            return back()->withErrors(['email' => 'Nie udało się zmienić hasła. Link mógł wygasnąć.']);
        }

        $status = Password::broker()->reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Nie udało się zmienić hasła. Link mógł wygasnąć.']);
        }

        return redirect()->route('customer.login')->with('status', 'Hasło zostało zmienione. Możesz się zalogować.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
