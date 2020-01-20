@if($leopards_settings)
    <div class="row">
        @foreach($leopards_settings as $leopards_setting)
            <div class="col-md-12">
                @if($leopards_setting->slug == 'mode')
                    <div class="form-group">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($leopards_setting->slug, ['0' => 'Producton', '1' => 'Test Mode'], $leopards_setting->data, ['class' => 'form-control', 'placeholder' => 'Select a Mode']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif(
                        $leopards_setting->slug == 'auto-fulfillment'
                    ||  $leopards_setting->slug == 'auto-mark-paid'
                )
                    {!! Form::hidden('name', $leopards_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <div class="form-group">
                        {!! Form::label('data', $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($leopards_setting->slug, ['0' => 'No', '1' => 'Yes'], $fulfillment_status, ['class' => 'form-control', 'placeholder' => 'Select an Option', 'required' => '']) !!}
                        @if($errors->has('data'))
                            <p class="help-block">
                                {{ $errors->first('data') }}
                            </p>
                        @endif
                    </div>
                @elseif($leopards_setting->slug == 'inventory-location')
                    {!! Form::hidden('name', $leopards_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <div class="form-group">
                        {!! Form::label('data', $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($leopards_setting->slug, $shopify_locations, $inventory_location, ['class' => 'form-control', 'placeholder' => 'Select a Location', 'required' => '']) !!}
                        @if($errors->has('data'))
                            <p class="help-block">
                                {{ $errors->first('data') }}
                            </p>
                        @endif
                    </div>
                @elseif($leopards_setting->slug == 'company-id')
                    <div class="form-group">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name . " (Optional, Will filled automatically after save)", ['class' => 'control-label']) !!}
                        {!! Form::text($leopards_setting->slug, $leopards_setting->data, ['class' => 'form-control', 'placeholder' => '', 'readonly' => 'true']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($leopards_setting->slug == 'password')
                    <div class="form-group">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::input('password', $leopards_setting->slug, $leopards_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($leopards_setting->slug == 'api-password')
                    <div class="form-group">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::input('password', $leopards_setting->slug, $leopards_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($leopards_setting->slug == 'shipper-type')
                    <div class="form-group">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($leopards_setting->slug, Config::get('constants.shipment_mode'), $leopards_setting->data, ['onchange' => 'FormValidation.cSM();', 'class' => 'form-control', 'placeholder' => 'Select a Shipment Mode', 'required' => '']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif(
                       $leopards_setting->slug == 'shipper-name'
                    || $leopards_setting->slug == 'shipper-phone'
                    || $leopards_setting->slug == 'shipper-email'
                    || $leopards_setting->slug == 'shipper-address'
                )
                    <div class="form-group {{ $leopards_setting->slug }}">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::text($leopards_setting->slug, $leopards_setting->data, ['class' => 'form-control', 'placeholder' => '']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @elseif($leopards_setting->slug == 'shipper-city')
                    <div class="form-group {{ $leopards_setting->slug }}">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::select($leopards_setting->slug, $leopards_cities, $leopards_setting->data, ['class' => 'form-control', 'placeholder' => 'Select a City']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="form-group">
                        {!! Form::label($leopards_setting->slug, $leopards_setting->name, ['class' => 'control-label']) !!}
                        {!! Form::text($leopards_setting->slug, $leopards_setting->data, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has($leopards_setting->slug))
                            <p class="help-block">
                                {{ $errors->first($leopards_setting->slug) }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="clearfix"></div>
@endif