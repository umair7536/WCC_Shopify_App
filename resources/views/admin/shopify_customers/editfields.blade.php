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
        {!! Form::label('email', 'Email*', ['class' => 'control-label']) !!}
        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
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