<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Logowanie | CopyCabana</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="flex min-h-screen items-center justify-center bg-[#063A60] p-5">
<form method="post" action="{{ route('admin.login.store') }}" class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
    @csrf
    <h1 class="text-2xl font-bold text-[#063A60]">Panel CopyCabana</h1>
    <p class="mt-2 text-sm text-slate-500">Zaloguj się jako administrator lub pracownik.</p>
    <label class="mt-6 block text-sm font-medium">E-mail<input name="email" type="email" required value="{{ old('email') }}" class="mt-1 w-full rounded border p-3"></label>
    <label class="mt-4 block text-sm font-medium">Hasło<input name="password" type="password" required class="mt-1 w-full rounded border p-3"></label>
    <label class="mt-4 flex gap-2 text-sm"><input type="checkbox" name="remember"> Zapamiętaj mnie</label>
    @error('email')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
    <button class="mt-6 w-full rounded bg-[#D51A70] px-4 py-3 font-semibold text-white">Zaloguj się</button>
</form>
</body>
</html>
