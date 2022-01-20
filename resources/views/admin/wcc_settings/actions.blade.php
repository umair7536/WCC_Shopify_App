@if($wcc_setting->slug == 'default')
    @if($wcc_setting->active)
        @if(Gate::allows('Wcc_settings_inactive'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.wcc_settings.inactive', $wcc_setting->id])) !!}
            {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
            {!! Form::close() !!}
        @endif
    @else
        @if(Gate::allows('Wcc_settings_active'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.wcc_settings.active', $wcc_setting->id])) !!}
            {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
            {!! Form::close() !!}
        @endif
    @endif
@endif
@if(Gate::allows('Wcc_settings_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.wcc_settings.edit',[$wcc_setting->id]) }}"
       data-target="#ajax_leopards_settings" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if($wcc_setting->slug == 'default')
    @if(Gate::allows('Wcc_settings_destroy'))
        {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'DELETE',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.wcc_settings.destroy', $wcc_setting->id])) !!}
        {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
        {!! Form::close() !!}
    @endif
@endif