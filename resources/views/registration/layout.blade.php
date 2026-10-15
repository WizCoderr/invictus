<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  {{-- resizes-content: on Android the keyboard shrinks the page, so the bottom action bar stays above it. --}}
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Register') — {{ config('registration.event_name') }}</title>
  {{-- Set explicitly: otherwise the browser falls back to the event's /favicon.ico. --}}
  <link rel="icon" type="image/png" href="{{ \App\Support\Asset::url('bni-icon.png') }}">
  <link rel="apple-touch-icon" href="{{ \App\Support\Asset::url('apple-touch-icon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="{{ \App\Support\Asset::url('css/register.css') }}">
</head>
<body>
  <div class="reg-shell">
    <aside class="reg-hero">
      <div class="reg-hero-inner">
        <div class="reg-logos">
          <span class="reg-logo"><img src="{{ \App\Support\Asset::url('bni-amritsar-logo.png') }}" alt="BNI Amritsar" width="900" height="182"></span>
          <span class="reg-logo"><img src="{{ \App\Support\Asset::url('bni-invictus-logo.png') }}" alt="BNI Invictus" width="900" height="194"></span>
        </div>
        @yield('aside')
      </div>
    </aside>

    <main class="reg-main">
      <div class="reg-main-inner">
        @yield('content')
      </div>

      <footer class="reg-foot">
        Developed with <span class="heart" aria-hidden="true">&hearts;</span> by
        <a href="https://peerinfotech.com/" target="_blank" rel="noopener noreferrer">
          <img src="{{ \App\Support\Asset::url('peerinfotech-logo.png') }}" alt="PEER INFOTECH">
        </a>
      </footer>
    </main>
  </div>

  @stack('scripts')
</body>
</html>
