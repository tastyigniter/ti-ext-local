<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">{{ $formTitle ? lang($formTitle) : '' }}</h4>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
    </div>
    {!! form_open([
      'id' => 'location-editor-form',
      'role' => 'form',
      'method' => 'PATCH',
      'data-request' => $this->alias.'::onSaveRecord',
    ]) !!}
    <div
      id="{{ $this->getId('form-modal-fields') }}"
      class="modal-body p-0 progress-indicator-container"
    >
      <div class="form-fields">
        <input type="hidden" name="recordId" value="{{ $formRecordId }}">
        @foreach($formWidget->getFields() as $field)
          {!! $formWidget->renderField($field) !!}
        @endforeach
      </div>
    </div>
    <div class="modal-footer text-right">
      @if($formWidget->getContext() == 'edit')
        <button
          type="button"
          class="btn btn-link text-danger text-decoration-none fw-bold mr-auto"
          data-request="{{ $this->alias }}::onDeleteRecord"
          data-request-confirm="@lang('igniter::admin.alert_warning_confirm')"
        >@lang('igniter::admin.button_delete')</button>
      @endif
      <button
        type="button"
        class="btn btn-link text-decoration-none fw-bold"
        data-bs-dismiss="modal"
      >@lang('igniter::admin.button_close')</button>
      <button
        type="submit"
        class="btn btn-primary"
        data-attach-loading
      >@lang('igniter::admin.button_save')</button>
    </div>
    {!! form_close() !!}
  </div>
</div>
