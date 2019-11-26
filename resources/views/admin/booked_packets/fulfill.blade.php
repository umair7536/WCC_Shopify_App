<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Fulfill Order <b>{{ $booked_packet->order_id }}</b></h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::open(['method' => 'POST', 'id' => 'form-fulfillment', 'route' => ['admin.booked_packets.savefulfillment', $booked_packet->id]]) !!}
                {{ Form::hidden('order_id', $shopify_order->order_id, ['id' => 'order_id']) }}
                <div class="form-body">
                    <!-- Starts Form Validation Messages -->
                    @include('partials.messages')
                    <!-- Ends Form Validation Messages -->

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('location_id', 'Location*', ['class' => 'control-label']) !!}
                            {!! Form::select('location_id', $shopify_locations, old('location_id'), ['id' => 'location_id', 'class' => 'form-control', 'placeholder' => 'Select a Location', 'required' => '']) !!}
                            @if($errors->has('location_id'))
                                <p class="help-block">
                                    {{ $errors->first('location_id') }}
                                </p>
                            @endif
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('track_number', 'Tacking Number', ['class' => 'control-label']) !!}
                            {!! Form::text('track_number', $booked_packet->track_number, ['readonly' => 'true', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('track_number'))
                                <p class="help-block">
                                    {{ $errors->first('track_number') }}
                                </p>
                            @endif
                        </div>

                        <div class="form-group col-md-12">
                            {!! Form::label('tracking_url', 'Tracking URL', ['class' => 'control-label']) !!}
                            {!! Form::text('tracking_url', route('track', $booked_packet->track_number), ['readonly' => 'true', 'class' => 'form-control', 'placeholder' => '']) !!}
                            @if($errors->has('tracking_url'))
                                <p class="help-block">
                                    {{ $errors->first('tracking_url') }}
                                </p>
                            @endif
                        </div>

                        <div class="form-group col-md-12">
                            {!! Form::label('notify_customer', 'Notify Customer of Shipment', ['class' => 'control-label']) !!}
                            <div class="mt-checkbox-inline">
                                <label class="mt-checkbox">
                                    <input name="notify_customer" type="checkbox" id="notify_customer" value="1"> Your customer will get notified when the order is fulfilled
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                    </div>

                </div>
                <div>
                    {!! Form::submit(trans('global.app_save'), ['class' => 'btn btn-success']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<script src="{{ url('js/admin/booked_packets/fulfillment.js') }}" type="text/javascript"></script>