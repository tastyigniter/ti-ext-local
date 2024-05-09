<li
  id="{{ $this->getId() }}"
  class="nav-item dropdown"
  data-control="location-picker"
>
@if($isSingleMode)
<a
    role="button"
    class="nav-link location"
    data-alias="locationpicker"
    data-toggle="record-editor"
    data-handler="{{ $this->getEventHandler('onLoadForm') }}"
    data-record-data='{"location": "{{ $activeLocation->location_id }}"}'
>
    <i class="fa fa-fw fa-location-dot"></i>
    <span>{{ $activeLocation->location_name }}</span>
</a>
    @else
  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <span
      class="fw-bold"
    >{{$activeLocation?->location_name ?? lang('igniter.local::default.text_select_location') }}</span>
    &nbsp;<i class="fa fa-xs fa-angle-down"></i>
  </a>

  <ul class="dropdown-menu dropdown-menu-start">
    @forelse($locations as $location)
      @php($isActive = $location->location_id == $activeLocation?->location_id)
      <li>
        <div
          @class(['dropdown-item d-flex align-items-center', 'active' => $isActive])
        >
          <a
            class="flex-fill text-reset"
            data-request="{{ $this->getEventHandler('onChoose') }}"
            data-request-data="location: '{{ $location->location_id }}'"
          >
            <i @class([
              'fa fa-fw fa-location-dot',
              'text-warning' => $location->is_default
            ])></i>
            <span>{{ $location->location_name }}</span>
          </a>
          <a
            class="fw-bold text-reset text-sm cursor-pointer"
            data-alias="locationpicker"
            href="{{ admin_url('locations/edit/'.$location->location_id) }}"
          >
            <span>@lang('igniter::admin.text_edit')</span>
          </a>
        </div>
      </li>
    @empty
      <li class="dropdown-item text-center">@lang('igniter.local::default.picker.text_no_location')</li>
    @endforelse
    @if($canCreateLocation)
      <li class="divider">
        <hr class="dropdown-divider">
      </li>
      <li class="dropdown-footer">
        <a
          class="dropdown-item border-0 text-left py-2 px-3"
          href="{{ admin_url('locations/create') }}"
      ><i class="fa fa-fw fa-plus-circle"></i>@lang('igniter.local::default.text_add_location')</a>
    </li>
    @endif
  </ul>
    @endif
</li>
