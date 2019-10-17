@if(Gate::allows('booked_packets_create'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'PATCH',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.booked_packets.cancel', $booked_packet->id])) !!}
    {!! Form::submit('Cancel', array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
<a class="btn btn-xs btn-warning" href="{{ route('admin.booked_packets.detail',[$booked_packet->id]) }}" data-target="#ajax_booked_packets" data-toggle="modal">View</a>