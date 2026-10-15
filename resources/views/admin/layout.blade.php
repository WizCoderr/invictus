<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') — {{ config('registration.event_name') }} admin</title>
  <link rel="icon" type="image/png" href="{{ \App\Support\Asset::url('bni-icon.png') }}">
  <link rel="apple-touch-icon" href="{{ \App\Support\Asset::url('apple-touch-icon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap">
  <link rel="stylesheet" href="{{ \App\Support\Asset::url('css/admin.css') }}">
</head>
<body>
  <div class="adm-shell">
    <aside class="adm-side">
      <a class="adm-brand" href="{{ route('admin.registrations') }}">
        <span class="adm-logo"><img src="{{ \App\Support\Asset::url('bni-amritsar-logo.png') }}" alt="BNI Amritsar" width="900" height="182"></span>
        <span class="adm-logo"><img src="{{ \App\Support\Asset::url('bni-invictus-logo.png') }}" alt="BNI Invictus" width="900" height="194"></span>
        <small>Admin</small>
      </a>

      <nav class="adm-nav">
        @foreach ([
          ['admin.registrations', 'Registrations', '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>'],
          ['admin.event-setup', 'Event setup', '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>'],
          ['admin.settings', 'Settings', '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>'],
        ] as [$route, $label, $icon])
          <a href="{{ route($route) }}" @class(['is-active' => request()->routeIs($route)])>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
            {{ $label }}
          </a>
        @endforeach
        <a href="{{ route('register') }}" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Open the form
        </a>
      </nav>

      <div class="adm-user">
        <span>{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit" class="link-btn">Sign out</button>
        </form>
      </div>
    </aside>

    <main class="adm-main">
      @if (session('status'))
        <div class="adm-flash" role="status">{{ session('status') }}</div>
      @endif

      @yield('content')
    </main>
  </div>

  @stack('scripts')
</body>
</html>
