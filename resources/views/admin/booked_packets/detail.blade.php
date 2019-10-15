<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th colspan="4">General Informaton</th>
        </tr>
        <tr>
            <th>ID</th>
            <td>{{ $booked_packet->customer_id }}</td>
            <th>Name</th>
            <td>{{ $booked_packet->first_name . ' ' . $booked_packet->last_name }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ ($booked_packet->phone) ? $booked_packet->phone : 'N/A' }}</td>
            <th>Email</th>
            <td>{{ ($booked_packet->email) ? $booked_packet->email : 'N/A' }}</td>
        </tr>
        <tr>
            <th colspan="4">Address Informaton</th>
        </tr>
        <tr>
            <th>Address</th>
            <td colspan="3">
                {{ ($booked_packet->address1) ? $booked_packet->address1 : 'N/A' }}
            </td>
        </tr>
        <tr>
            <th>City</th>
            <td>{{ ($booked_packet->city) ? $booked_packet->city : 'N/A' }}</td>
            <th>Province</th>
            <td>{{ ($booked_packet->province) ? $booked_packet->province : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Zip</th>
            <td>{{ ($booked_packet->zip) ? $booked_packet->zip : 'N/A' }}</td>
            <th>Country</th>
            <td>{{ ($booked_packet->country) ? $booked_packet->country : 'N/A' }}</td>
        </tr>
        </tbody>
    </table>
</div>