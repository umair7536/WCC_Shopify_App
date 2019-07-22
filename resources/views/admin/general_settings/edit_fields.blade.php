<div class="row">
    <div class="col-md-12">
        @if($general_setting->slug == 'bookin' || $general_setting->slug == 'repair')
            {!! Form::hidden('name', $general_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <div class="form-group">
                {!! Form::label('data', $general_setting->name, ['class' => 'control-label']) !!}
                {!! Form::select('data[]', $custom_collections, old('data'), ['class' => 'form-control select2', 'multiple' => 'multiple', 'placeholder' => 'Keep Empty this Option']) !!}
                @if($errors->has('data'))
                    <p class="help-block">
                        {{ $errors->first('data') }}
                    </p>
                @endif
            </div>
        @else
            {!! Form::hidden('name', $general_setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <div class="form-group">
                {!! Form::label('data', $general_setting->name, ['class' => 'control-label']) !!}
                {!! Form::textarea('data', old('data'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
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