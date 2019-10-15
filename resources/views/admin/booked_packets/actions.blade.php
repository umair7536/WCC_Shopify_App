@if(Gate::allows('booked_packets_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.booked_packets.edit',[$booked_packet->id]) }}"
       data-target="#ajax_booked_packets" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('booked_packets_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.booked_packets.destroy', $booked_packet->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
<a class="btn btn-xs btn-warning" href="{{ route('admin.booked_packets.detail',[$booked_packet->id]) }}">View</a>