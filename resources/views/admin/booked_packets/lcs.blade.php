<style>
    .remarkTable td {
        padding: 5px !important;
        line-height: 14px;
    }

    #abcdef a {
        color: darkblue;
        text-decoration: underline;
    }
</style>
<style type="text/css">
    #bgSetter {
        background: url({{ env('LCS_URL') }}images/statusImages/track_bg.png) center center no-repeat;
        background-size: 80%;
    }

    .corier_van {
        background: url({{ env('LCS_URL') }}images/statusImages/van.png) no-repeat;
        height: 90px;
        width: 220px;
        background-size: 100% 100%;
        display: table-cell;
        text-align: left;
        font-size: 10px;
        font-weight: bold;
        font-size: 12px;
    }

    .corier_van p {
        padding-top: 28px;
        padding-left: 12px;
        text-align: center;
        width: 135px;
    }

    .corier_van input[type=text] {
        width: 110px;
        font-size: 12px;
        font-weight: bold;
        border: none;
        background: none;
    }
</style>
<div style=" width: 900px;  overflow-x: hidden;" id="bgSetter">
    <table width="100%" border="0" cellspacing="0" cellpadding="0"
           style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td width="33%" valign="middle" align="center">
                <p style="padding-bottom: 8px;">From</p>
                <h1>{{ isset($packet['origin_city_name']) ? $packet['origin_city_name'] : 'N/A'  }}</h1>
            </td>
            <td width="34%" align="center">
                <p style="padding-bottom: 8px;">Packet Status</p>
                <h1>{{ isset($packet['booked_packet_status']) ? $packet['booked_packet_status'] : 'N/A'  }}</h1>
            </td>
            <td width="33%" valign="middle" align="center">
                <p style="padding-bottom: 8px;">To</p>
                <h1>{{ isset($packet['destination_city_name']) ? $packet['destination_city_name'] : 'N/A'  }}</h1>
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="5" cellspacing="0" class="id-form remarkTable"
           style="font-family:Arial, Helvetica, sans-serif; margin: 0 auto; font-size: 11px; width: 100%; border-collapse: collapse; border-top: 1px solid gray;">
        <tr>
            <td width="20%" valign="top" align="right">
                <b>Tracking #:</b>
            </td>
            <td width="30%" valign="top">
                {{ isset($packet['track_number']) ? $packet['track_number'] : 'N/A' }}
            </td>
            <td width="20%" valign="top" align="right">
                <b>Reference # / Order ID:</b>
            </td>
            <td width="30%" valign="top">
                {{ ( isset($packet['booked_packet_order_id']) && $packet['booked_packet_order_id']) ? $packet['booked_packet_order_id'] : 'N/A' }}
            </td>
        </tr>
        <tr>
            <td width="15%" valign="top" align="right">
                <b>No. of Pieces:</b>
            </td>
            <td width="35%" valign="top">
                {{ isset($packet['booked_packet_no_piece']) ? $packet['booked_packet_no_piece'] : 'N/A' }}
            </td>
            <td valign="top" align="right">
                <b>Packet Weight:</b>
            </td>
            <td valign="top">
                {{ isset($packet['booked_packet_weight']) ? number_format($packet['booked_packet_weight'] / 1000, 3) . ' (Kgs)' : 'N/A' }}
            </td>
        </tr>
        <tr>
            <td valign="top" align="right">
                <b>Shipper Name:</b>
            </td>
            <td valign="top">
                {{ isset($packet['shipment_name_eng']) ? $packet['shipment_name_eng'] : 'N/A' }}
            </td>
            <td valign="top" align="right">
                <b>Consignee Name:</b>
            </td>
            <td valign="top">
                {{ isset($packet['consignment_name_eng']) ? $packet['consignment_name_eng'] : 'N/A' }}
            </td>
        </tr>
        <tr>
            <td width="15%" valign="top" align="right">
                <b>Shipper Address:</b>
            </td>
            <td width="35%" valign="top">
                {{ isset($packet['origin_city_name']) ? $packet['origin_city_name'] . ', ' . $packet['origin_country_name'] : 'N/A' }}
            </td>
            <td valign="top" align="right">
                <b>Consignee Address:</b>
            </td>
            <td valign="top">
                {{ isset($packet['destination_city_name']) ? $packet['destination_city_name'] . ', ' . $packet['origin_country_name'] : 'N/A' }}
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="5" cellspacing="0" class="id-form remarkTable"
           style="font-family:Arial, Helvetica, sans-serif; margin: 0 auto; font-size: 11px; width: 100%; border: 1px solid gray; border-collapse: collapse;">
        <tr style="background-color: #D8D8D8;">
            <td style="border-left: 1px solid gray; width: 150px !important;" valign="top">
                Activity Date
            </td>
            <td style="border-left: 1px solid gray;" valign="top">
                Status
            </td>
        </tr>
        @if(count($track_history))
            @foreach($track_history as $track_line)
                <tr style="border: 1px solid gray;">
                    <td width="30%" valign="top" style="border-left: 1px solid gray; width: 150px;">{{ \Carbon\Carbon::parse($track_line['Activity_datetime'])->format('M j, Y h:i A') }}</td>
                    <td valign="top" style="border-left: 1px solid gray;">{{ $track_line['Status'] }} {{ (isset($track_line['Reason']) && $track_line['Reason']) ? $track_line['Reason'] : '' }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2">No Tracking informaiton found.</td>
            </tr>
        @endif
    </table>
</div>
