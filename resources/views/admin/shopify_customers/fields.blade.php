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
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('address1', 'Address', ['class' => 'control-label']) !!}
        {!! Form::text('address1', old('address1'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('address1'))
            <p class="help-block">
                {{ $errors->first('address1') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('city', 'City', ['class' => 'control-label']) !!}
        {!! Form::text('city', old('city'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('city'))
            <p class="help-block">
                {{ $errors->first('city') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('province', 'Province', ['class' => 'control-label']) !!}
        {!! Form::text('province', old('province'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('province'))
            <p class="help-block">
                {{ $errors->first('province') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('zip', 'Zip', ['class' => 'control-label']) !!}
        {!! Form::text('zip', old('zip'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('zip'))
            <p class="help-block">
                {{ $errors->first('zip') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('country', 'Country', ['class' => 'control-label']) !!}
        {!! Form::text('country', old('country'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('country'))
            <p class="help-block">
                {{ $errors->first('country') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <div class="mt-checkbox-inline">
            <label class="mt-checkbox">
                <input name="email_verified" checked="checked" type="checkbox" id="email_verified" value="1"> Verified Email
                <span></span>
            </label>
        </div>
    </div>
    <div class="form-group col-md-6">
        <div class="mt-checkbox-inline">
            <label class="mt-checkbox">
                <input name="password_confirmation" type="checkbox" id="password_confirmation" value="1"> Set Password
                <span></span>
            </label>
        </div>
    </div>
</div>
<div class="row password_confirmation">
    <div class="form-group col-md-6">
        {!! Form::label('password', 'Password', ['class' => 'control-label']) !!}
        <input type="password" id="password" name="password" value="{{ old('password') }}" class="form-control" />
        @if($errors->has('password'))
            <p class="help-block">
                {{ $errors->first('password') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('send_email_welcome', 'Welcome Email', ['class' => 'control-label']) !!}
        <div class="mt-checkbox-inline">
            <label class="mt-checkbox">
                <input name="send_email_welcome" type="checkbox" id="send_email_welcome" value="1"> Send Welcome Email to Customer
                <span></span>
            </label>
        </div>
    </div>
</div>
<div class="clearfix"></div>