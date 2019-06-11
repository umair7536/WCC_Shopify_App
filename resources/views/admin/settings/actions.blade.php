@if(Gate::allows('settings_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.settings.edit',[$setting->id]) }}" data-target="#ajax_setting" data-toggle="modal">@lang('global.app_edit')</a>
@endif
<!-- {!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'DELETE',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.settings.destroy', $setting->id])) !!}
{!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
{!! Form::close() !!} -->