@extends('registration.layout')

@section('title', 'Registration confirmed')

@section('aside')
  <p class="reg-kicker">{{ config('registration.event_name') }}</p>
  <h1 class="reg-title">You're all set</h1>
  <p class="reg-tagline">Keep your reference handy — it's how the organisers will find your registration.</p>
@endsection

@section('content')
  <div class="reg-card reg-done">
    <div class="reg-done-mark" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </div>

    <h2 class="reg-done-title">You're registered, {{ \Illuminate\Support\Str::before($registration->name, ' ') }}!</h2>
    <p class="reg-hint">Your place at <strong>{{ config('registration.event_name') }}</strong> as a {{ $registration->type }} is confirmed.</p>

    <div class="reg-ref">
      <span>Reference</span>
      <strong>{{ $registration->reference }}</strong>
    </div>

    <dl class="reg-summary">
      <div><dt>Name</dt><dd>{{ $registration->name }}</dd></div>
      <div><dt>Attending as</dt><dd>{{ ucfirst($registration->type) }}</dd></div>
      <div><dt>Business</dt><dd>{{ $registration->businessCategory->name }}</dd></div>
      @if ($registration->chapter)
        <div><dt>Chapter</dt><dd>{{ $registration->chapter->name }}</dd></div>
      @endif
      @if ($registration->phone)
        <div><dt>Mobile</dt><dd>{{ $registration->phone }}</dd></div>
      @endif
      @if ($registration->email)
        <div><dt>Email</dt><dd>{{ $registration->email }}</dd></div>
      @endif
      @if ($registration->invited_by)
        <div><dt>Invited by</dt><dd>{{ $registration->invited_by }}</dd></div>
      @endif
    </dl>

    @if ($registration->total_amount > 0)
      <div class="reg-bill">
        @if ((float) $registration->gst_amount > 0)
          <div><span>Registration fee</span><span>{{ \App\Support\Money::inr((float) $registration->amount) }}</span></div>
          <div><span>GST</span><span>{{ \App\Support\Money::inr((float) $registration->gst_amount) }}</span></div>
        @else
          <div><span>Registration fee (Incl. GST)</span><span>{{ \App\Support\Money::inr((float) $registration->amount) }}</span></div>
        @endif
        <div class="is-total"><span>Paid</span><span>{{ \App\Support\Money::inr((float) $registration->total_amount) }}</span></div>
      </div>
    @endif

    <div class="reg-nav">
      <button type="button" class="reg-btn reg-btn-ghost" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print / save
      </button>
    </div>
  </div>
@endsection
