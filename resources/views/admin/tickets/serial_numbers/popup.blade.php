<div class="modal-header">
    <button type="button" id="closeBtn" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Other @lang('global.tickets.fields.ticket_plural') of <b class="font-blue-madison">{{ $serial_number }}</b></h4>
</div>
<div class="modal-body" style="max-height: 400px; overflow-y: auto;">
    <div class="form-body">

        <div class="table-container">
            <table class="table table-striped table-bordered table-checkable" id="datatable_ajax">
                <tbody>
                    @if($tickets->count())
                        @foreach($tickets as $single_ticket)
                            <tr>
                                <th width="40%">Ticket #</th>
                                <td>{{ $single_ticket->number }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ \Carbon\Carbon::parse($single_ticket->created_at)->format('F j, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Parts</th>
                                <td>
                                    @if($ticket_repairs)
                                        @foreach($ticket_repairs as $ticket_repair)
                                            @if($ticket_repair->ticket_id == $single_ticket->id)
                                                <ul>
                                                    <li>{{ $ticket_repair->title . (($ticket_repair->serial_number) ? ' - ' . $ticket_repair->serial_number : '') }}</li>
                                                </ul>
                                            @endif
                                        @endforeach
                                    @else
                                        Parts not avilable.
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Comments</th>
                                <td>{{ $single_ticket->technician_remarks }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;"></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>Other @lang('global.tickets.fields.ticket_plural') not found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>
