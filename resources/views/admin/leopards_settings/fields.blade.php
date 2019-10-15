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