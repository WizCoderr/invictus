@extends('admin.layout')

@section('title', 'Settings')

@section('content')
  <div class="page-head">
    <h1>Settings</h1>
    <p>What registrants pay. Changes apply to the next registration straight away.</p>
  </div>

  <form method="POST" action="{{ route('admin.settings.update') }}" class="panel">
    @csrf
    @method('PUT')

    <h2>Registration fees</h2>
    <p class="sub">Before GST. A fee of 0 skips payment — the registration is confirmed on the spot.</p>

    <div class="form-grid">
      @foreach (['member_fee' => 'Member fee (₹)', 'visitor_fee' => 'Visitor fee (₹)', 'gst_percent' => 'GST (%)'] as $key => $label)
        <div class="field">
          <label for="{{ $key }}">{{ $label }}</label>
          <input type="number" id="{{ $key }}" name="{{ $key }}" required min="0" step="0.01" inputmode="decimal"
                 @if ($key === 'gst_percent') max="100" @endif
                 value="{{ old($key, rtrim(rtrim(number_format($values[$key], 2, '.', ''), '0'), '.')) }}">
          @error($key) <span class="err">{{ $message }}</span> @enderror
        </div>
      @endforeach
    </div>

    <button type="submit" class="btn">Save settings</button>
  </form>
@endsection
