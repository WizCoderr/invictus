@extends('registration.layout')

@section('aside')
  @if ($invitedBy !== '')
    <p class="reg-kicker">You're invited</p>
    <h1 class="reg-title">{{ config('registration.event_name') }}</h1>
    <p class="reg-tagline">{{ $invitedBy }} has invited you as their guest. Registering takes about a minute.</p>
  @else
    <p class="reg-kicker">Registration open</p>
    <h1 class="reg-title">{{ config('registration.event_name') }}</h1>
    <p class="reg-tagline">Register as a member or a visitor — it takes about a minute.</p>
  @endif

  <ol class="reg-steps" id="wizard-steps" aria-label="Registration progress">
    <li data-for="1"><span class="reg-dot">1</span><span class="reg-step-text"><b>Your name</b><small>As printed on your badge</small></span></li>
    <li data-for="2"><span class="reg-dot">2</span><span class="reg-step-text"><b>Attending as</b><small>Member or visitor</small></span></li>
    <li data-for="3"><span class="reg-dot">3</span><span class="reg-step-text"><b>Business</b><small>Your line of work</small></span></li>
    <li data-for="4"><span class="reg-dot">4</span><span class="reg-step-text"><b>Details</b><small>Chapter or contact info</small></span></li>
    <li data-for="5"><span class="reg-dot">5</span><span class="reg-step-text"><b>Review &amp; pay</b><small>Confirm your seat</small></span></li>
  </ol>
@endsection

@section('content')
  <div class="reg-card" id="wizard-card">
    <div class="reg-progress" aria-hidden="true"><span id="wizard-bar"></span></div>
    <p class="reg-eyebrow" id="wizard-count">Step 1 of 5</p>

    <div class="reg-alert" id="wizard-error" role="alert" hidden></div>

    <form id="wizard-form" novalidate>
      <section class="wizard-step" data-step="1">
        <label class="reg-q" for="reg_name">What's your full name?</label>
        <p class="reg-hint">We'll print it on your badge exactly as you type it.</p>
        <div class="reg-field">
          <input type="text" id="reg_name" name="name" required autocomplete="name" autocapitalize="words" enterkeyhint="next" maxlength="120" placeholder="Kashiv Seth">
          <span class="reg-err" hidden></span>
        </div>
      </section>

      <section class="wizard-step" data-step="2" hidden>
        <fieldset>
          <legend class="reg-q">How are you attending?</legend>
          <p class="reg-hint">Pick the one that fits — you can change it before you pay.</p>

          <div class="reg-choices">
            @foreach (['member' => ['Member', "I'm a member of a chapter", 'users'], 'visitor' => ['Visitor', "I've been invited by a member", 'user-plus']] as $value => [$title, $sub, $icon])
              <label class="reg-choice">
                <input type="radio" name="type" value="{{ $value }}" @if ($loop->first) required @endif
                       @checked($invitedBy !== '' && $value === 'visitor')>
                <span class="reg-choice-icon" aria-hidden="true">
                  @if ($icon === 'users')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                  @endif
                </span>
                <span class="reg-choice-body">
                  <span class="reg-choice-title">{{ $title }}</span>
                  <span class="reg-choice-sub">{{ $sub }}</span>
                </span>
                <span class="reg-choice-price">
                  @if ($fees[$value]['total_amount'] > 0)
                    {{ \App\Support\Money::inr($fees[$value]['amount']) }}<small>+ GST</small>
                  @else
                    Free
                  @endif
                </span>
              </label>
            @endforeach
          </div>
        </fieldset>
      </section>

      <section class="wizard-step" data-step="3" hidden>
        <fieldset>
          <legend class="reg-q">What's your line of business?</legend>
          <p class="reg-hint">Search, then pick the category that fits you best.</p>

          @if ($businessCategories->isEmpty())
            <p class="reg-note">Categories haven't been set up yet — please check back shortly.</p>
          @else
            <div class="reg-field">
              <div class="reg-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" id="reg_category_search" placeholder="Search categories" autocomplete="off" enterkeyhint="search"
                       aria-label="Search business categories" aria-controls="reg_category_list">
              </div>

              <div class="reg-list" id="reg_category_list">
                @foreach ($businessCategories as $category)
                  <label class="reg-option">
                    <input type="radio" name="business_category_id" value="{{ $category->id }}" required>
                    <span>{{ $category->name }}</span>
                  </label>
                @endforeach
                <p class="reg-list-empty" id="reg_category_empty" hidden>No category matches that search.</p>
              </div>
              <span class="reg-err" hidden></span>
            </div>
          @endif
        </fieldset>
      </section>

      <section class="wizard-step" data-step="4-member" hidden>
        <fieldset>
          <legend class="reg-q">Which chapter are you from?</legend>
          <p class="reg-hint">Choose the chapter you're a member of.</p>

          @if ($chapters->isEmpty())
            <p class="reg-note">Chapters haven't been set up yet — please check back shortly.</p>
          @else
            <div class="reg-field">
              <div class="reg-tiles">
                @foreach ($chapters as $chapter)
                  <label class="reg-tile">
                    <input type="radio" name="chapter_id" value="{{ $chapter->id }}" data-required-when="member">
                    <span>{{ $chapter->name }}</span>
                  </label>
                @endforeach
              </div>
              <span class="reg-err" hidden></span>
            </div>
          @endif
        </fieldset>
      </section>

      <section class="wizard-step" data-step="4-visitor" hidden>
        <p class="reg-q">How can we reach you?</p>
        <p class="reg-hint">We'll send your confirmation and event updates here.</p>

        <div class="reg-field">
          <label for="reg_phone">Mobile number</label>
          <input type="tel" id="reg_phone" name="phone" data-required-when="visitor" autocomplete="tel" inputmode="tel" enterkeyhint="next" maxlength="20" placeholder="98765 43210">
          <span class="reg-err" hidden></span>
        </div>
        <div class="reg-field">
          <label for="reg_email">Email address</label>
          <input type="email" id="reg_email" name="email" data-required-when="visitor" autocomplete="email" enterkeyhint="next" maxlength="120" placeholder="you@company.com">
          <span class="reg-err" hidden></span>
        </div>
        <div class="reg-field">
          <label for="reg_invited_by">Invited by <span class="reg-optional">Optional</span></label>
          <input type="text" id="reg_invited_by" name="invited_by" value="{{ $invitedBy }}" autocapitalize="words" enterkeyhint="next" maxlength="120" placeholder="Name of the member who invited you">
          <span class="reg-err" hidden></span>
        </div>
      </section>

      <section class="wizard-step" data-step="5" hidden>
        <p class="reg-q">Review &amp; confirm</p>
        <p class="reg-hint">Check everything looks right — tap Edit to change anything.</p>

        <dl class="reg-summary" id="wizard-summary"></dl>
        <div class="reg-bill" id="wizard-fee"></div>

        <p class="reg-secure" id="wizard-secure-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
          Secure checkout by Razorpay — UPI, cards and netbanking.
        </p>
      </section>

      <div class="reg-nav is-sticky">
        <button type="button" class="reg-btn reg-btn-ghost" id="wizard-back" hidden>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
          <span class="reg-btn-label">Back</span>
        </button>
        <button type="submit" class="reg-btn reg-btn-primary" id="wizard-next">Continue</button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    window.REGISTRATION = {
      event: @json(config('registration.event_name')),
      urls: {
        store: @json(route('register.store')),
        paid: @json(route('register.paid')),
        status: @json(route('register.status'))
      },
      fees: @json($fees)
    };
  </script>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script src="{{ \App\Support\Asset::url('js/registration-form.js') }}"></script>
@endpush
