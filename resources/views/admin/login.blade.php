<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in — {{ config('registration.event_name') }} admin</title>
  <link rel="icon" href="data:,">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap">
  <link rel="stylesheet" href="{{ \App\Support\Asset::url('css/admin.css') }}">
</head>
<body class="adm-login">
  <main class="adm-login-card">
    <span class="adm-mark" aria-hidden="true">I</span>
    <h1>{{ config('registration.event_name') }}</h1>
    <p class="sub">Organiser sign in</p>

    <form method="POST" action="{{ route('admin.login.attempt') }}">
      @csrf

      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email') <span class="err">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        @error('password') <span class="err">{{ $message }}</span> @enderror
      </div>

      <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>

      <button class="btn btn-block" type="submit">Sign in</button>
    </form>
  </main>
</body>
</html>
