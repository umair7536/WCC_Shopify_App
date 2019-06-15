@if($ticket->active)
    @if(Gate::allows('tickets_inactive'))
        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'PATCH',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.tickets.inactive', $ticket->id])) !!}
        {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
        {!! Form::close() !!}
    @endif
@else
    @if(Gate::allows('tickets_active'))
        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'PATCH',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.tickets.active', $ticket->id])) !!}
        {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
        {!! Form::close() !!}
    @endif
@endif
@if(Gate::allows('tickets_manage'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.tickets.detail',[$ticket->id]) }}"
       data-target="#ajax_tickets"
       data-toggle="modal"><i class="fa fa-eye"></i></a>
@endif
@if(Gate::allows('tickets_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.tickets.edit',[$ticket->id]) }}"><i class="fa fa-edit"></i></a>
@endif
@if(Gate::allows('tickets_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.tickets.destroy', $ticket->id])) !!}
    {!! Form::button('<i class="fa fa-trash"></i>', array('type' => 'submit', 'class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
<a class="btn btn-xs btn-warning" target="_blank" href="{{ route('admin.tickets.draft_order',[$ticket->id]) }}"><i class="fa fa-money"></i> </a>