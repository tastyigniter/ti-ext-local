<div id="importContainer" class="import-container">
    {!! $importPrimaryFormWidget->render() !!}
    @if ($importSecondaryFormWidget)
        {!! $importSecondaryFormWidget->render() !!}
    @endif
</div>
