@if(Gate::allows('leopards_cities_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.leopards_cities.edit',[$leopards_citie->id]) }}"
       data-target="#ajax_leopards_cities" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('leopards_cities_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.leopards_cities.destroy', $leopards_citie->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif