@if(
    Config::get('constants.request_sent') == $booked_packet->status
)
    @if(Gate::allows('booked_packets_create'))
        {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.booked_packets.cancel', $booked_packet->id])) !!}
        {!! Form::button("<i class=\"fa fa-close\"></i> Cancel", array('type' => 'submit', 'class' => 'btn btn-xs btn-danger margin-bottom-5')) !!}
        {!! Form::close() !!}
    @endif
@endif
<a class="btn btn-xs btn-primary margin-bottom-5" onclick="TableDatatablesAjax.setLoader('ajax_booked_packets');" href="{{ route('admin.booked_packets.track',[$booked_packet->id]) }}" data-target="#ajax_booked_packets" data-toggle="modal"><i class="fa fa-road"></i> Track</a>
<a class="btn btn-xs btn-warning margin-bottom-5" onclick="TableDatatablesAjax.setLoader('ajax_booked_packets_detail');" href="{{ route('admin.booked_packets.detail',[$booked_packet->id]) }}" data-target="#ajax_booked_packets_detail" data-toggle="modal"><i class="fa fa-eye"></i> View</a>
<a class="btn btn-xs btn-success margin-bottom-5" target="_blank" href="{{ $booked_packet->slip_link }}"><i class="fa fa-print"></i> Print Slip</a>
<a class="btn btn-xs btn-danger margin-bottom-5" onclick="TableDatatablesAjax.setLoader('ajax_booked_packets_fulfill');" href="{{ route('admin.booked_packets.fulfill',[$booked_packet->id]) }}" data-target="#ajax_booked_packets_fulfill" data-toggle="modal"><i class="fa fa-send"></i> Fulfill</a>