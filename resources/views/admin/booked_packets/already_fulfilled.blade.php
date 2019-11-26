<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Fulfill Order <b>{{ $booked_packet->order_id }}</b></h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            This order is already fulfilled. Details are below.
        </div>
        <div class="table-container">
            <table class="table table-striped table-bordered">
                @foreach($fullfilments as $fullfilment)
                    <tr>
                        <th>Tracking #</th>
                        <td>{{ $fullfilment['tracking_number'] }}</td>
                    </tr>
                    <tr>
                        <th>Tracking URL</th>
                        <td>
                            <a href="{{ $fullfilment['tracking_url'] }}" target="_blank">{{ $fullfilment['tracking_url'] }} <i class="fa fa-external-link"></i></a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
<script src="{{ url('js/admin/booked_packets/fulfillment.js') }}" type="text/javascript"></script>