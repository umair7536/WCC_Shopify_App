@if(isset($ticket_products_mapping[$ticket->id]))
    @foreach($ticket_products_mapping[$ticket->id] as $serial_number)
        <a id="ticket{{ $ticket->id }}" href="{{ route('admin.tickets.showserialnumberhistory',['id' => $ticket->id, 'serial_number' => $serial_number]) }}" data-target="#ajax_tickets" data-toggle="modal">{{ $serial_number }}</a><br/>
    @endforeach
@else
    N/A
@endif