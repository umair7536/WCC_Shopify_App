<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
            <tr>
                <th colspan="4" style="text-align: center;">General Informaton</th>
            </tr>
            <tr>
                <th>Type</th>
                <td>{{ $shipment_type[$booked_packet->shipment_type_id] }}</td>
                <th>Booking Date</th>
                <td>{{ $booked_packet->booking_date }}</td>
            </tr>
            <tr>
                <th>Pieces</th>
                <td>{{ $booked_packet->packet_pieces }}</td>
                <th>Weight (grms)</th>
                <td>{{ $booked_packet->net_weight }}</td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>{{ number_format($booked_packet->collect_amount, 2) }}</td>
                <th>Order ID</th>
                <td>{{ $booked_packet->order_id }}</td>
            </tr>
            <tr>
                <th>Vol. Dimension</th>
                <td colspan="3">
                    @if($booked_packet->vol_weight_w && $booked_packet->vol_weight_h && $booked_packet->vol_weight_h)
                        {{ ($booked_packet->vol_weight_w * $booked_packet->vol_weight_h * $booked_packet->vol_weight_l) / 5000 }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: center;">Shipper Informaton</th>
            </tr>
            <!-- ISSUE IN THIS PART   START -->            
            <tr>
                <th>Origin</th>

                <td>{{$wcc_cities}}</td>
                <th>Shipper Name</th>
                <td>{{ $booked_packet->shipper_name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $booked_packet->shipper_email }}</td>
                <th>Phone</th>
                <td>{{ $booked_packet->shipper_phone }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="3">{{ $booked_packet->shipper_address }}</td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: center;">Consignee Informaton</th>
            </tr>
            <tr>
                <th>Destination</th>

                <td>{{$wcc_cities}}</td>
                <th>Shipper Name</th>
                <td>{{ $booked_packet->consignee_name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $booked_packet->consignee_email }}</td>
                <th>Phone</th>
                <td>{{ $booked_packet->consignee_phone }}</td>
            </tr>
            <tr>
                <th>PHone 2</th>
                <td>{{ $booked_packet->consignee_phone_two }}</td>
                <th>Phone 3</th>
                <td>{{ $booked_packet->consignee_phone_three }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="3">{{ $booked_packet->consignee_address }}</td>
            </tr>

            <!-- ISSUE IN THIS PART   END -->
        </tbody>
    </table>
</div>