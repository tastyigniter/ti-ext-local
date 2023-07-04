<div class="row no-gutters mb-3">
  @foreach($settings as $group => $setting)
    <div class="col-lg-4">
      <a
        class="text-reset d-block p-3 h-100"
        @if($setting->url)
          href="{{ $setting->url }}"
        @else
          data-toggle="record-editor"
        data-handler="{{ $this->getEventHandler('onLoadRecord') }}"
        data-record-data='{"code": "{{$setting->code}}"}'
        @endif
        role="button"
      >
        <div class="card bg-light shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="pr-3">
              <h5>
                <div class="rounded-circle bg-light">
                  @if ($setting->icon)
                    <i class="text-muted {{ $setting->icon }} fa-fw"></i>
                  @else
                    <i class="text-muted fa fa-puzzle-piece fa-fw"></i>
                  @endif
                </div>
              </h5>
            </div>
            <div class="">
              <h5>@lang($setting->label)</h5>
              <p class="no-margin text-muted">{!! $setting->description ? lang($setting->description) : '' !!}</p>
            </div>
          </div>
        </div>
      </a>
    </div>
  @endforeach
</div>