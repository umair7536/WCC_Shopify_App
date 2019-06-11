{!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
<div class="form-group">
    {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
    {!! Form::textarea('data', old('data'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    @if($errors->has('data'))
        <p class="help-block">
            {{ $errors->first('data') }}
        </p>
    @endif
</div>