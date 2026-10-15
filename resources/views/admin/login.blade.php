<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in — {{ config('registration.event_name') }} admin</title>
  <link rel="icon" type="image/png" href="{{ \App\Support\Asset::url('bni-icon.png') }}">
  <link rel="apple-touch-icon" href="{{ \App\Support\Asset::url('apple-touch-icon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap">
  <link rel="stylesheet" href="{{ \App\Support\Asset::url('css/admin.css') }}">
</head>
<body class="adm-login">
  <div class="adm-login-shell">
    <aside class="adm-login-brand">
      <div class="adm-login-brand-inner">
        <div class="adm-login-logos">
          <span><img src="{{ \App\Support\Asset::url('bni-amritsar-logo.png') }}" alt="BNI Amritsar" width="900" height="182"></span>
          <span><img src="{{ \App\Support\Asset::url('bni-invictus-logo.png') }}" alt="BNI Invictus" width="900" height="194"></span>
        </div>
        <p class="adm-login-kicker">Organiser console</p>
        <h1>{{ config('registration.event_name') }}</h1>
        <p class="adm-login-tagline">Track registrations, share the sign-up QR code and set up the event.</p>
      </div>
    </aside>

    <main class="adm-login-main">
      <div class="adm-login-form">
        <h2>Welcome back</h2>
        <p class="sub">Sign in with your organiser account.</p>

        <form method="POST" action="{{ route('admin.login.attempt') }}">
          @csrf

          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="username">
            @error('email') <span class="err">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            @error('password') <span class="err">{{ $message }}</span> @enderror
          </div>

          <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>

          <button class="btn btn-block btn-lg" type="submit">Sign in</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
