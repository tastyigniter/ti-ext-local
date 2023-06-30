<li
  id="{{ $this->getId() }}"
  class="nav-item dropdown"
  data-control="location-picker"
>
  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <span
      class="fw-bold"
    >{{$activeLocation?->location_name ?? lang('igniter.local::default.text_select_location') }}</span>
    &nbsp;<i class="fa fa-xs fa-angle-down"></i>
  </a>

  <ul class="dropdown-menu dropdown-menu-start">
    @foreach($locations as $location)
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
              'text-muted' => !$location->is_default,
              'text-warning' => $location->is_default
            ])></i>
            <span>{{ $location->location_name }}</span>
          </a>
          <a
            class="fw-bold text-reset text-sm cursor-pointer"
            data-alias="locationpicker"
            data-toggle="record-editor"
            data-handler="{{ $this->getEventHandler('onLoadForm') }}"
            data-record-data='{"location": "{{ $location->location_id }}"}'
          >
            <span>@lang('igniter::admin.text_edit')</span>
          </a>
        </div>
      </li>
    @endforeach
    <li class="divider">
      <hr class="dropdown-divider">
    </li>
    <li class="dropdown-footer">
      <button
        type="button"
        class="dropdown-item"
        data-alias="locationpicker"
        data-toggle="record-editor"
        data-handler="{{ $this->getEventHandler('onLoadForm') }}"
      ><i class="fa fa-fw fa-plus-circle"></i>@lang('igniter.local::default.text_add_location')</button>
    </li>
  </ul>
</li>
