@extends('admin.layout')

@section('title', 'Registrations')

@section('content')
  <div class="page-head">
    <h1>Registrations ({{ $registrations->count() }})</h1>
    <p>Everyone who has signed up through the public registration form.</p>
  </div>

  <div class="panel qr-panel">
    <img class="qr-code" id="register-qr" src="{{ $qrSrc }}" width="168" height="168"
         alt="QR code that opens the registration form">

    <div class="qr-body">
      @if ($invitedBy !== '')
        <h2>Invite QR for {{ $invitedBy }}</h2>
        <p class="sub">Guests who scan this get the form with <strong>Invited by: {{ $invitedBy }}</strong> already filled in.</p>
      @else
        <h2>Registration QR code</h2>
        <p class="sub">Scanning it opens the registration form. Print it on posters and standees, or share the image.</p>
      @endif
      <a class="qr-link" href="{{ $registerUrl }}" target="_blank" rel="noopener">{{ $registerUrl }}</a>

      <form class="qr-invite" method="GET" action="{{ route('admin.registrations') }}">
        <input type="text" name="invited_by" value="{{ $invitedBy }}" maxlength="120"
               placeholder="Member's name, for their personal invite QR" aria-label="Member's name">
        <button type="submit" class="btn btn-quiet">Make invite QR</button>
        @if ($invitedBy !== '')
          <a class="link-btn" href="{{ route('admin.registrations') }}">Back to the general QR</a>
        @endif
      </form>

      <div class="qr-actions">
        <button type="button" class="btn" id="qr-png">Download PNG</button>
        <a class="btn btn-quiet" href="{{ $qrSrc }}" download="{{ $qrFile }}.svg">Download SVG</a>
        <button type="button" class="btn btn-quiet" id="qr-copy">Copy link</button>
      </div>
    </div>
  </div>

  <div class="panel">
    @if ($registrations->isEmpty())
      <div class="empty">
        Nobody has registered yet. Share the QR code above, or the <a href="{{ $registerUrl }}" target="_blank" rel="noopener">registration link</a>.
      </div>
    @else
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Reference</th>
              <th>Name</th>
              <th>Type</th>
              <th>Category</th>
              <th>Chapter / Contact</th>
              <th>Invited by</th>
              <th>Amount</th>
              <th>Payment</th>
              <th>Registered</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($registrations as $registration)
              <tr>
                <td data-label="Reference"><span class="ref-code">{{ $registration->reference }}</span></td>
                <td data-label="Name">{{ $registration->name }}</td>
                <td data-label="Type">{{ ucfirst($registration->type) }}</td>
                <td data-label="Category">{{ $registration->businessCategory->name }}</td>
                <td data-label="Chapter / Contact">
                  @if ($registration->chapter)
                    {{ $registration->chapter->name }}
                  @else
                    <span>
                      {{ $registration->phone ?? '—' }}
                      @if ($registration->email) <span class="cell-note">{{ $registration->email }}</span> @endif
                    </span>
                  @endif
                </td>
                <td data-label="Invited by">{{ $registration->invited_by ?? '—' }}</td>
                <td data-label="Amount">{{ \App\Support\Money::inr((float) $registration->total_amount) }}</td>
                <td data-label="Payment">
                  <span class="tag {{ $registration->payment_status }}">
                    {{ $registration->payment_status === \App\Models\Registration::PAYMENT_PAID ? 'Paid' : 'Not paid' }}
                  </span>
                </td>
                <td data-label="Registered">{{ $registration->created_at?->format('d M Y, H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      var url = @json($registerUrl);
      var copy = document.getElementById('qr-copy');

      copy.addEventListener('click', function () {
        // The clipboard API only exists on https (or localhost); elsewhere, offer the link to copy by hand.
        if (!navigator.clipboard) { window.prompt('Copy this link:', url); return; }

        navigator.clipboard.writeText(url).then(function () {
          copy.textContent = 'Copied!';
          setTimeout(function () { copy.textContent = 'Copy link'; }, 1600);
        });
      });

      // A print-size PNG for WhatsApp and printers that won't take SVG, drawn from the code on screen.
      document.getElementById('qr-png').addEventListener('click', function () {
        var canvas = document.createElement('canvas');
        canvas.width = canvas.height = 1200;
        canvas.getContext('2d').drawImage(document.getElementById('register-qr'), 0, 0, 1200, 1200);

        var link = document.createElement('a');
        link.download = @json($qrFile . '.png');
        link.href = canvas.toDataURL('image/png');
        link.click();
      });
    })();
  </script>
@endpush
