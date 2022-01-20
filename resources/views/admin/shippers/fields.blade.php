<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('city_id', 'City*', ['class' => 'control-label']) !!}
        {!! Form::select('city_id', $wcc_cities, old('city_id'), ['class' => 'form-control inpt-focus select2', 'placeholder' => 'Select a City', 'required' => '']) !!}
        @if($errors->has('city_id'))
            <p class="help-block">
                {{ $errors->first('city_id') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email*', ['class' => 'control-label']) !!}
        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('email'))
            <p class="help-block">
                {{ $errors->first('email') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('phone', 'Phone*', ['class' => 'control-label']) !!}
        {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('phone'))
            <p class="help-block">
                {{ $errors->first('phone') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('address', 'Address*', ['class' => 'control-label']) !!}
        {!! Form::textarea('address', old('address') , ['rows' => '3', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('address'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>