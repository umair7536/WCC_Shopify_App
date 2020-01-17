<h4>Basic Information</h4>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('shipment_type_id', 'Shipment Type*', ['class' => 'control-label']) !!}
        {!! Form::select('shipment_type_id', $shipment_type, $default_shipment_type, ['class' => 'form-control inpt-focus', 'placeholder' => 'Select a Shipment Type', 'required' => '']) !!}
        @if($errors->has('shipment_type_id'))
            <p class="help-block">
                {{ $errors->first('shipment_type_id') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('booking_date', 'Booking Date*', ['class' => 'control-label']) !!}
        {!! Form::text('booking_date', $booking_date, ['readonly' => 'true', 'class' => 'form-control date-picker', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('booking_date'))
            <p class="help-block">
                {{ $errors->first('booking_date') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('packet_pieces', 'No. of Pieces*', ['class' => 'control-label']) !!}
        {!! Form::number('packet_pieces', $data['booked_packet']['packet_pieces'], ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('packet_pieces'))
            <p class="help-block">
                {{ $errors->first('packet_pieces') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('net_weight', 'Net Weight (grams)*', ['class' => 'control-label']) !!}
        <div class="input-group">
            {!! Form::number('net_weight', $data['booked_packet']['net_weight'], ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <span class="input-group-addon">(Approx.)</span>
        </div>
        @if($errors->has('net_weight'))
            <p class="help-block">
                {{ $errors->first('net_weight') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('collect_amount', 'COD Amount (PKR)*', ['class' => 'control-label']) !!}
        {!! Form::number('collect_amount', $data['booked_packet']['collect_amount'], ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('collect_amount'))
            <p class="help-block">
                {{ $errors->first('collect_amount') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::hidden('order_number', $data['booked_packet']['order_number']) !!}
        {!! Form::label('order_id', 'Order ID', ['class' => 'control-label']) !!}
        {!! Form::text('order_id', $data['booked_packet']['order_id'], ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('order_id'))
            <p class="help-block">
                {{ $errors->first('order_id') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('volumetric_dimensions', 'Volumetric Dimensions (cm)', ['class' => 'control-label']) !!}
        <div class="input-group">
            {!! Form::number('vol_weight_w', old('vol_weight_w'), ['onkeyup' => 'BookedPacketValidation.calculateVol();', 'id' => 'vol_weight_w', 'class' => 'form-control', 'placeholder' => 'Width']) !!}
            <span class="input-group-addon">X</span>
            {!! Form::number('vol_weight_h', old('vol_weight_h'), ['onkeyup' => 'BookedPacketValidation.calculateVol();', 'id' => 'vol_weight_h', 'class' => 'form-control', 'placeholder' => 'Height']) !!}
            <span class="input-group-addon">X</span>
            {!! Form::number('vol_weight_l', old('vol_weight_l'), ['onkeyup' => 'BookedPacketValidation.calculateVol();', 'id' => 'vol_weight_l', 'class' => 'form-control', 'placeholder' => 'Length']) !!}
        </div>
        @if($errors->has('address1'))
            <p class="help-block">
                {{ $errors->first('address1') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('volumetric_dimensions_calculated', 'Volumetric Weight', ['class' => 'control-label']) !!}
        {!! Form::text('volumetric_dimensions_calculated', $volumetric_dimensions_calculated, ['id' => 'volumetric_dimensions_calculated', 'readonly' => 'true', 'class' => 'form-control', 'placeholder' => 'Width']) !!}
    </div>
</div>
<div class="clearfix"></div>
<h4>Shipper Information</h4>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('shipper_id', 'Shipper*', ['class' => 'control-label']) !!}
        {!! Form::select('shipper_id', $shippers, $data['booked_packet']['shipper_id'], ['onchange' => 'BookedPacketValidation.changeShipper($(this).val());', 'id' => 'shipper_id', 'class' => 'form-control', 'placeholder' => 'Select a Shipper', 'required' => '']) !!}
        @if($errors->has('shipper_id'))
            <p class="help-block">
                {{ $errors->first('shipper_id') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('origin_city', 'Origin City*', ['class' => 'control-label']) !!}
        {!! Form::select('origin_city', $leopards_cities, $data['booked_packet']['origin_city'], ['id' => 'origin_city', 'class' => 'form-control select2', 'placeholder' => 'Select Origin City', 'required' => '']) !!}
        @if($errors->has('origin_city'))
            <p class="help-block">
                {{ $errors->first('origin_city') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('shipper_name', 'Shipper Name*', ['class' => 'control-label']) !!}
        {!! Form::text('shipper_name', $data['booked_packet']['shipper_name'], ['id' => 'shipper_name', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('shipper_name'))
            <p class="help-block">
                {{ $errors->first('shipper_name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('shipper_email', 'Shipper Email', ['class' => 'control-label']) !!}
        {!! Form::text('shipper_email', $data['booked_packet']['shipper_email'], ['id' => 'shipper_email', 'class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('shipper_email'))
            <p class="help-block">
                {{ $errors->first('shipper_email') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('shipper_phone', 'Shipper Phone*', ['class' => 'control-label']) !!}
        {!! Form::text('shipper_phone', $data['booked_packet']['shipper_phone'], ['id' => 'shipper_phone', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('shipper_phone'))
            <p class="help-block">
                {{ $errors->first('shipper_phone') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('shipper_address', 'Shipper Address*', ['class' => 'control-label']) !!}
        {!! Form::textarea('shipper_address', $data['booked_packet']['shipper_address'], ['id' => 'shipper_address', 'rows' => '3', 'class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('shipper_address'))
            <p class="help-block">
                {{ $errors->first('shipper_address') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<h4>Consignee Information</h4>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('consignee_id', 'Consignee*', ['class' => 'control-label']) !!}
        {!! Form::select('consignee_id', $consignees, $consignee_id, ['class' => 'form-control', 'placeholder' => 'Select a Consignee', 'required' => '']) !!}
        @if($errors->has('consignee_id'))
            <p class="help-block">
                {{ $errors->first('consignee_id') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('destination_city', 'Destination City*', ['class' => 'control-label']) !!}
        {!! Form::select('destination_city', $leopards_cities, $data['booked_packet']['destination_city'], ['class' => 'form-control select2', 'placeholder' => 'Select Destination City', 'required' => '']) !!}
        @if($errors->has('destination_city'))
            <p class="help-block">
                {{ $errors->first('destination_city') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('consignee_name', 'Consignee Name*', ['class' => 'control-label']) !!}
        {!! Form::text('consignee_name', $data['booked_packet']['consignee_name'], ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('consignee_name'))
            <p class="help-block">
                {{ $errors->first('consignee_name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('consignee_email', 'Consignee Email', ['class' => 'control-label']) !!}
        {!! Form::text('consignee_email', $data['booked_packet']['consignee_email'], ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('consignee_email'))
            <p class="help-block">
                {{ $errors->first('consignee_email') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('consignee_phone', 'Consignee Phone*', ['class' => 'control-label']) !!}
        {!! Form::text('consignee_phone', $data['booked_packet']['consignee_phone'], ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('consignee_phone'))
            <p class="help-block">
                {{ $errors->first('consignee_phone') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('consignee_phone_2', 'Consignee Phone 2', ['class' => 'control-label']) !!}
        {!! Form::text('consignee_phone_2', old('consignee_phone_2'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('consignee_phone_2'))
            <p class="help-block">
                {{ $errors->first('consignee_phone_2') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('consignee_phone_3', 'Consignee Phone 3', ['class' => 'control-label']) !!}
        {!! Form::text('consignee_phone_3', old('consignee_phone_3'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('consignee_phone_3'))
            <p class="help-block">
                {{ $errors->first('consignee_phone_3') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('consignee_address', 'Consignee Address*', ['class' => 'control-label']) !!}
        {!! Form::textarea('consignee_address', $data['booked_packet']['consignee_address'], ['rows' => '3', 'class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('consignee_address'))
            <p class="help-block">
                {{ $errors->first('consignee_address') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<hr/>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('comments', 'Special Instructions*', ['class' => 'control-label']) !!}
        {!! Form::textarea('comments', $data['booked_packet']['comments'], ['rows' => '3', 'class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('comments'))
            <p class="help-block">
                {{ $errors->first('comments') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>