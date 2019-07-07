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
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('price', 'Price*', ['class' => 'control-label']) !!}
        {!! Form::number('price', old('price'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('price'))
            <p class="help-block">
                {{ $errors->first('price') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('quota', 'Quota*', ['class' => 'control-label']) !!}
        {!! Form::number('quota', old('quota'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('quota'))
            <p class="help-block">
                {{ $errors->first('quota') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>