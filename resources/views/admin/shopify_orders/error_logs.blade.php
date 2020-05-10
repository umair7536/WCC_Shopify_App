<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Error Logs</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th>Order #</th>
            <th>Message</th>
            <th>Date</th>
        </tr>
        @if($order_logs->count())
            @foreach($order_logs as $order_log)
                <tr>
                    <td>{{ $order_log->name }}</td>
                    <td>{!! $order_log->message !!}</td>
                    <td>{!! \Carbon\Carbon::parse($order_log->created_at)->format('M j, Y h:i A') !!}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2">No order log found.</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>