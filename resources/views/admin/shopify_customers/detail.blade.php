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
            <td>{{ $shopify_customer->customer_id }}</td>
            <th>Name</th>
            <td>{{ $shopify_customer->first_name . ' ' . $shopify_customer->last_name }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ ($shopify_customer->phone) ? $shopify_customer->phone : 'N/A' }}</td>
            <th>Email</th>
            <td>{{ ($shopify_customer->email) ? $shopify_customer->email : 'N/A' }}</td>
        </tr>
        <tr>
            <th colspan="4">Address Informaton</th>
        </tr>
        <tr>
            <th>Address</th>
            <td colspan="3">
                {{ ($shopify_customer->address1) ? $shopify_customer->address1 : 'N/A' }}
            </td>
        </tr>
        <tr>
            <th>City</th>
            <td>{{ ($shopify_customer->city) ? $shopify_customer->city : 'N/A' }}</td>
            <th>Province</th>
            <td>{{ ($shopify_customer->province) ? $shopify_customer->province : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Zip</th>
            <td>{{ ($shopify_customer->zip) ? $shopify_customer->zip : 'N/A' }}</td>
            <th>Country</th>
            <td>{{ ($shopify_customer->country) ? $shopify_customer->country : 'N/A' }}</td>
        </tr>
        </tbody>
    </table>
</div>