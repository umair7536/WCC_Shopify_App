{{--@if($usertype->active)--}}
    {{--{!! Form::open(array(--}}
    {{--'style' => 'display: inline-block;',--}}
    {{--'method' => 'PATCH',--}}
    {{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
    {{--'route' => ['admin.user_types.inactive', $usertype->id])) !!}--}}
    {{--{!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}--}}
    {{--{!! Form::close() !!}--}}
{{--@else--}}
    {{--{!! Form::open(array(--}}
    {{--'style' => 'display: inline-block;',--}}
    {{--'method' => 'PATCH',--}}
    {{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
    {{--'route' => ['admin.user_types.active', $usertype->id])) !!}--}}
    {{--{!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}--}}
    {{--{!! Form::close() !!}--}}
{{--@endif--}}
@if(Gate::allows('user_types_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.user_types.edit',[$usertype->id]) }}" data-target="#ajax_usertype_edit" data-toggle="modal">@lang('global.app_edit')</a>
@endif
{{--{!! Form::open(array(--}}
    {{--'style' => 'display: inline-block;',--}}
    {{--'method' => 'DELETE',--}}
    {{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
    {{--'route' => ['admin.user_types.destroy', $usertype->id])) !!}--}}
{{--{!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}--}}
{{--{!! Form::close() !!}--}}
