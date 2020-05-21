<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th colspan="4">Load Sheet Information</th>
        </tr>
        <tr>
            <th>Sheet ID</th>
            <td>{{ $load_sheet->load_sheet_id }}</td>
            <th>Total Products</th>
            <td>{{ $load_sheet->total_packets }}</td>
        </tr>
        <tr>
            <th>Created At</th>
            <td>{{ \Carbon\Carbon::parse($load_sheet->created_at)->format('M j, Y h:i A') }}</td>
        </tr>
        </tbody>
    </table>
    <div class="table-scrollable">
        <table class="table table-striped" style="height:100px !important; overflow-y: scroll !important;">
            <tbody>
            <tr>
                <th colspan="2">Load Sheet Packets</th>
            </tr>
            <tr>
                <th>Order #</th>
                <th>CN #</th>
            </tr>
            @if($load_sheet_packets)
                @foreach($load_sheet_packets as $load_sheet_packet)
                    <tr>
                        <td>{{ $load_sheet_packet->order_id }}</td>
                        <td>{{ $load_sheet_packet->cn_number }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2">No data found.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>