<div class="row">
    <div class="col-md-12">
        @if($leopards_setting->slug == 'mode')
            {!! Form::hidden('name', $leopards_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <div class="form-group">
                {!! Form::label('data', $leopards_setting->name, ['class' => 'control-label']) !!}
                {!! Form::select('data', ['0' => 'Producton', '1' => 'Test Mode'], old('data'), ['class' => 'form-control', 'placeholder' => 'Select a Mode']) !!}
                @if($errors->has('data'))
                    <p class="help-block">
                        {{ $errors->first('data') }}
                    </p>
                @endif
            </div>
        @else
            {!! Form::hidden('name', $leopards_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <div class="form-group">
                {!! Form::label('data', $leopards_setting->name, ['class' => 'control-label']) !!}
                {!! Form::text('data', old('data'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                @if($errors->has('data'))
                    <p class="help-block">
                        {{ $errors->first('data') }}
                    </p>
                @endif
            </div>
        @endif
    </div>
</div>
<div class="clearfix"></div>