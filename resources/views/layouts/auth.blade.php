<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Logowanie | CopyCabana')</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&amp;family=Roboto+Slab:wght@600;700&amp;display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; background: #fff; color: #3f4a54; font-family: Roboto, Arial, sans-serif; line-height: 1.5; }
    .auth-stripe { display: flex; height: 6px; }
    .auth-stripe span { flex: 1; }
    .auth-main { display: flex; justify-content: center; padding: 2.5rem 1.25rem 4rem; }
    .auth-wrap { width: 100%; max-width: 28rem; }
    .auth-logo { display: flex; justify-content: center; margin-bottom: 2rem; }
    .auth-logo img { width: 150px; height: auto; }
    .auth-card { border: 1px solid #d9dde1; border-radius: 1rem; padding: 2rem; }
    .auth-card h1 { margin: 0; color: #063a60; font-family: 'Roboto Slab', Georgia, serif; font-size: 1.75rem; line-height: 1.2; }
    .auth-card p.lead { margin: .5rem 0 0; }
    .auth-form { display: flex; flex-direction: column; gap: 1.1rem; margin-top: 1.5rem; }
    .auth-form label { display: flex; flex-direction: column; gap: .35rem; font-weight: 500; color: #063a60; }
    .auth-form input { width: 100%; padding: .75rem .9rem; border: 1px solid #b9c0c7; border-radius: .6rem; font: inherit; color: #1f2933; background: #fff; }
    .auth-form input:focus { outline: 3px solid rgba(255, 237, 0, .7); border-color: #063a60; }
    .auth-button { width: 100%; padding: .85rem 1rem; border: 0; border-radius: .6rem; background: #063a60; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
    .auth-button:hover { background: #00456f; }
    .auth-error { margin: 0; color: #b42318; font-size: .9rem; }
    .auth-status { margin-top: 1.25rem; padding: .75rem 1rem; border-left: 4px solid #7fbf45; background: #f2f9ec; color: #2f5d12; }
    .auth-link { display: inline-block; margin-top: 1.5rem; color: #00456f; }
  </style>
</head>
<body>
  <div class="auth-stripe" aria-hidden="true">
    <span style="background:#00A0DF"></span><span style="background:#D51A70"></span><span style="background:#FFED00"></span><span style="background:#333"></span>
  </div>
  <main class="auth-main">
    <div class="auth-wrap">
      <a href="{{ route('home') }}" class="auth-logo" aria-label="CopyCabana">
        <img src="{{ asset('images/logo.png') }}" alt="CopyCabana">
      </a>
      @yield('content')
    </div>
  </main>
</body>
</html>
