@if($general_setting->slug == 'default')
    @if($general_setting->active)
        @if(Gate::allows('general_settings_inactive'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.general_settings.inactive', $general_setting->id])) !!}
            {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
            {!! Form::close() !!}
        @endif
    @else
        @if(Gate::allows('general_settings_active'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.general_settings.active', $general_setting->id])) !!}
            {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
            {!! Form::close() !!}
        @endif
    @endif
@endif
@if(Gate::allows('general_settings_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.general_settings.edit',[$general_setting->id]) }}"
       data-target="#ajax_general_settings" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if($general_setting->slug == 'default')
    @if(Gate::allows('general_settings_destroy'))
        {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'DELETE',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.general_settings.destroy', $general_setting->id])) !!}
        {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
        {!! Form::close() !!}
    @endif
@endif