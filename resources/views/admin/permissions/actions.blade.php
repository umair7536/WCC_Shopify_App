@if(Gate::allows('permissions_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.permissions.edit',[$permission->id]) }}" data-target="#ajax_permission" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('permissions_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.permissions.destroy', $permission->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif