<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('first_name', 'First Name*', ['class' => 'control-label']) !!}
        {!! Form::text('first_name', old('first_name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('first_name'))
            <p class="help-block">
                {{ $errors->first('first_name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('last_name', 'Last name*', ['class' => 'control-label']) !!}
        {!! Form::text('last_name', old('last_name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('last_name'))
            <p class="help-block">
                {{ $errors->first('last_name') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email', ['class' => 'control-label']) !!}
        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('email'))
            <p class="help-block">
                {{ $errors->first('email') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('phone', 'Phone', ['class' => 'control-label']) !!}
        {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('phone'))
            <p class="help-block">
                {{ $errors->first('phone') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('city', 'City*', ['class' => 'control-label']) !!}
        {!! Form::select('city', $leopards_cities, old('city'), ['class' => 'form-control', 'placeholder' => 'Select a City', 'required' => '']) !!}
        @if($errors->has('city'))
            <p class="help-block">
                {{ $errors->first('city') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('address1', 'Address 1*', ['class' => 'control-label']) !!}
        {!! Form::text('address1', old('address1'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('address1'))
            <p class="help-block">
                {{ $errors->first('address1') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('address2', 'Address 2', ['class' => 'control-label']) !!}
        {!! Form::text('address2', old('address2'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('address2'))
            <p class="help-block">
                {{ $errors->first('address2') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>