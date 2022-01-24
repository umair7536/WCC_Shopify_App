@if($wcc_settings)
    <div class="row">
        @foreach($wcc_settings as $wcc_setting)
            <div class="col-md-12">
                @if($wcc_setting->slug == 'mode')
                    <!-- <div class="form-group">
                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($wcc_setting->slug, ['0' => 'Producton', '1' => 'Test Mode'], $wcc_setting->data, ['class' => 'form-control', 'placeholder' => 'Select a Mode']) !!}
                        @if($errors->has($wcc_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($wcc_setting->slug) }}
                            </p>
                        @endif
                    </div> -->
                @elseif($wcc_setting->slug == 'auto-fulfillment')
                    {!! Form::hidden('name', $wcc_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <div class="form-group">
                        {!! Form::label('data', $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($wcc_setting->slug, ['0' => 'No', '1' => 'Yes'], $fulfillment_status, ['class' => 'form-control', 'placeholder' => 'Select an Option', 'required' => '']) !!}
                        @if($errors->has('data'))
                            <p class="help-block">
                                {{ $errors->first('data') }}
                            </p>
                        @endif
                    </div>
                @elseif($wcc_setting->slug == 'auto-mark-paid')
                    {!! Form::hidden('name', $wcc_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <!-- <div class="form-group">
                        {!! Form::label('data', $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($wcc_setting->slug, ['0' => 'No', '1' => 'Yes'], $mark_status, ['class' => 'form-control', 'placeholder' => 'Select an Option', 'required' => '']) !!}
                        @if($errors->has('data'))
                            <p class="help-block">
                                {{ $errors->first('data') }}
                            </p>
                        @endif
                    </div> -->
                @elseif($wcc_setting->slug == 'inventory-location')
                    {!! Form::hidden('name', $wcc_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <div class="form-group">
                        {!! Form::label('data', $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($wcc_setting->slug, $shopify_locations, $inventory_location, ['class' => 'form-control', 'placeholder' => 'Select a Location', 'required' => '']) !!}
                        @if($errors->has('data'))
                            <p class="help-block">
                                {{ $errors->first('data') }}
                            </p>
                        @endif
                    </div>
                @elseif($wcc_setting->slug == 'company-id')
                    {{--                    <div class="form-group">--}}
                    {{--                        {!! Form::label($wcc_setting->slug, $wcc_setting->name . " (Optional, Will filled automatically after save)", ['class' => 'control-label']) !!}--}}
                    {{--                        {!! Form::text($wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '', 'readonly' => 'true']) !!}--}}
                    {{--                        @if($errors->has($wcc_setting->slug))--}}
                    {{--                            <p class="help-block">--}}
                    {{--                                {{ $errors->first($wcc_setting->slug) }}--}}
                    {{--                            </p>--}}
                    {{--                        @endif--}}
                    {{--                    </div>--}}
                @elseif($wcc_setting->slug == 'username')
                    <div class="form-group {{ $wcc_setting->slug }}">
                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::text($wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '']) !!}
                        @if($errors->has($wcc_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($wcc_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($wcc_setting->slug == 'password')
                    <div class="form-group">
                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::input('password', $wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has($wcc_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($wcc_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($wcc_setting->slug == 'api-password')
                    {{--                    <div class="form-group">--}}
                    {{--                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}--}}
                    {{--                        {!! Form::input('password', $wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}--}}
                    {{--                        @if($errors->has($wcc_setting->slug))--}}
                    {{--                            <p class="help-block">--}}
                    {{--                                {{ $errors->first($wcc_setting->slug) }}--}}
                    {{--                            </p>--}}
                    {{--                        @endif--}}
                    {{--                    </div>--}}
                @elseif($wcc_setting->slug == 'shipper-type')
                    <div class="form-group">
                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($wcc_setting->slug, Config::get('constants.shipment_mode'), $wcc_setting->data, ['onchange' => 'FormValidation.cSM();', 'class' => 'form-control', 'placeholder' => 'Select a Shipment Mode', 'required' => '']) !!}
                        @if($errors->has($wcc_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($wcc_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif(
                       $wcc_setting->slug == 'shipper-name'
                    || $wcc_setting->slug == 'shipper-phone'
                    || $wcc_setting->slug == 'shipper-email'
                    || $wcc_setting->slug == 'shipper-address'
                )
                    <div class="form-group {{ $wcc_setting->slug }}">
                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}
                        @if($wcc_setting->data)
                            {{--                            {!! Form::text($wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '']) !!}--}}
                            {!! Form::text($wcc_setting->slug,$wcc_setting->data , ['class' => 'form-control', 'placeholder' => '']) !!}

                        @else
                            {!! Form::text($wcc_setting->slug, 'self', ['class' => 'form-control', 'placeholder' => '']) !!}

                        @endif

                        @if($errors->has($wcc_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($wcc_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($wcc_setting->slug == 'shipper-city')
                    <div class="form-group {{ $wcc_setting->slug }}">
                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}

                            {!! Form::select($wcc_setting->slug, $wcc_cities, '', ['class' => 'form-control', 'placeholder' => 'Select a City']) !!}
{{--                        @endif--}}
                        @if($errors->has($wcc_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($wcc_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($wcc_setting->slug == 'username')
                    {{--                    <div class="form-group">--}}
                    {{--                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}--}}
                    {{--                        {!! Form::text($wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}--}}
                    {{--                        @if($errors->has($wcc_setting->slug))--}}
                    {{--                            <p class="help-block">--}}
                    {{--                                {{ $errors->first($wcc_setting->slug) }}--}}
                    {{--                            </p>--}}
                    {{--                        @endif--}}
                    {{--                    </div>--}}
                @else
                    {{--                    <div class="form-group">--}}
                    {{--                        {!! Form::label($wcc_setting->slug, $wcc_setting->name, ['class' => 'control-label']) !!}--}}
                    {{--                        {!! Form::text($wcc_setting->slug, $wcc_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}--}}
                    {{--                        @if($errors->has($wcc_setting->slug))--}}
                    {{--                            <p class="help-block">--}}
                    {{--                                {{ $errors->first($wcc_setting->slug) }}--}}
                    {{--                            </p>--}}
                    {{--                        @endif--}}
                    {{--                    </div>--}}
                @endif
            </div>
        @endforeach
    </div>
    <div class="clearfix"></div>
@endif
