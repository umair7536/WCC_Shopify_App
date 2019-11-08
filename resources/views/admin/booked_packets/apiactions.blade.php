@if(Gate::allows('booked_packets_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.booked_packets.destroy', $booked_packet->id])) !!}
    {!! Form::button('<i class="fa fa-trash"></i> ' . trans('global.app_delete'), array('type' => 'submit', 'class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
<a class="btn btn-xs btn-warning" href="{{ route('admin.booked_packets.detail',[$booked_packet->id]) }}" data-target="#ajax_booked_packets" data-toggle="modal"><i class="fa fa-eye"></i> View</a>
<a class="btn btn-xs btn-success" target="_blank" href="{{ $booked_packet->slip_link }}"><i class="fa fa-print"></i> Print Slip</a>