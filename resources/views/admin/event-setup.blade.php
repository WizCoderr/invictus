@extends('admin.layout')

@section('title', 'Event setup')

@section('content')
  <div class="page-head">
    <h1>Event setup</h1>
    <p>The chapters and business categories a registrant picks from on the registration form.</p>
  </div>

  @foreach ([
    'chapter' => ['Chapters', $chapters, route('admin.chapters.store'), 'admin.chapters.destroy', 'e.g. Solis', 'Add chapter'],
    'category' => ['Business categories', $businessCategories, route('admin.business-categories.store'), 'admin.business-categories.destroy', 'e.g. Real Estate', 'Add category'],
  ] as $bag => [$title, $items, $storeUrl, $destroyRoute, $placeholder, $button])
    <div class="panel">
      <h2>{{ $title }}</h2>

      <form method="POST" action="{{ $storeUrl }}" class="inline-form">
        @csrf
        <input type="text" name="name" maxlength="120" required placeholder="{{ $placeholder }}"
               value="{{ $errors->getBag($bag)->any() ? old('name') : '' }}" aria-label="{{ $button }}">
        <button class="btn" type="submit">{{ $button }}</button>
      </form>
      @error('name', $bag) <p class="err">{{ $message }}</p> @enderror

      @if ($items->isEmpty())
        <div class="empty">Nothing here yet — add the ones registrants can choose from above.</div>
      @else
        <div class="table-wrap">
          <table>
            <thead><tr><th>Name</th><th>Registrations</th><th></th></tr></thead>
            <tbody>
              @foreach ($items as $item)
                <tr>
                  <td data-label="Name">{{ $item->name }}</td>
                  <td data-label="Registrations">{{ $item->registrations_count }}</td>
                  <td class="col-actions">
                    <form method="POST" action="{{ route($destroyRoute, $item) }}"
                          onsubmit="return confirm(@js('Delete "'.$item->name.'"?'));">
                      @csrf @method('DELETE')
                      <button type="submit" class="link-btn is-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  @endforeach
@endsection
