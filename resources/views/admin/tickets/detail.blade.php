<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th width="25%">Number</th>
            <td>{{ $ticket->number }}</td>
            <th width="25%">Total Products</th>
            <td>{{ number_format($ticket->total_products) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td colspan="3">{{ $ticket->ticket_status->name }}</td>
        </tr>
        <tr>
            <th>Notes for Technician</th>
            <td colspan="3">{{ $ticket->technician_remarks }}</td>
        </tr>
        <tr>
            <th>Feedback/ Complain from Customer</th>
            <td colspan="3">{{ $ticket->customer_complain }}</td>
        </tr>
        </tbody>
    </table>

    <table class="table table-striped">
        <tbody>
        <tr>
            <th>Image</th>
            <th>Product</th>
        </tr>
        </tbody>
        <tbody>
            @if($ticket_products)
                @foreach($ticket_products as $ticket_product)
                    <tr>
                        <td width="10%"><img src="{{ $ticket_product->image_src }}" width="60" /></td>
                        <td>{{ $ticket_product->title }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>