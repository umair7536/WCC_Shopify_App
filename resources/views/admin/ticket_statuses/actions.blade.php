@if($ticket_statuse->slug == 'default')
    @if($ticket_statuse->active)
        @if(Gate::allows('ticket_statuses_inactive'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.ticket_statuses.inactive', $ticket_statuse->id])) !!}
            {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
            {!! Form::close() !!}
        @endif
    @else
        @if(Gate::allows('ticket_statuses_active'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.ticket_statuses.active', $ticket_statuse->id])) !!}
            {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
            {!! Form::close() !!}
        @endif
    @endif
@endif
@if(Gate::allows('ticket_statuses_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.ticket_statuses.edit',[$ticket_statuse->id]) }}"
       data-target="#ajax_ticket_statuses" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if($ticket_statuse->slug == 'default')
    @if(Gate::allows('ticket_statuses_destroy'))
        {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'DELETE',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.ticket_statuses.destroy', $ticket_statuse->id])) !!}
        {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
        {!! Form::close() !!}
    @endif
@endif