@if(Gate::allows('load_sheets_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.load_sheets.detail',[$load_sheet->id]) }}" data-target="#ajax_load_sheet" data-toggle="modal"><i class="fa fa-eye"></i> View</a>
    <a class="btn btn-xs btn-success" href="{{ route('admin.load_sheets.download',[$load_sheet->id]) }}" target="_blank"><i class="fa fa-download"></i> Download</a>
@endif
{{--{!! Form::open(array(--}}
{{--    'style' => 'display: inline-block;',--}}
{{--    'method' => 'DELETE',--}}
{{--    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
{{--    'route' => ['admin.load_sheets.destroy', $load_sheet->id])) !!}--}}
{{--{!! Form::submit('<i class="fa fa-download"></i> Download', array('class' => 'btn btn-xs btn-danger')) !!}--}}
{{--{!! Form::close() !!}--}}