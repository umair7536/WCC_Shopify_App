<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('Data', 'Data*', ['class' => 'control-label']) !!}
        {!! Form::text('data', old('data'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>