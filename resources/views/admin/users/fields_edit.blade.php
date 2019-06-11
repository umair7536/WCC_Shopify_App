<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        <p class="help-block"></p>
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email*', ['class' => 'control-label']) !!}
        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        <p class="help-block"></p>
        @if($errors->has('email'))
            <p class="help-block">
                {{ $errors->first('email') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('phone', 'Phone*', ['class' => 'control-label']) !!}
        {!! Form::number('phone', (old('phone')) ? old('phone') : $user->phone, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('phone'))
            <p class="help-block">
                {{ $errors->first('phone') }}
            </p>
        @endif
    </div>
    <div class="col-md-6 form-group">
        {!! Form::label('roles', 'Roles*', ['class' => 'control-label']) !!}
        @php($user_roles = $user->roles()->pluck('id'))
        @if($user_roles)
            @php($user_roles = $user_roles->toArray())
        @else
            @php($user_roles = array())
        @endif
        <select name="roles[]" multiple class="form-control roles" required>
            @foreach($roles as $key => $value)
                <option @if(in_array($key, $user_roles)) selected="selected"
                        @endif value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
        @if($errors->has('roles'))
            <p class="help-block">
                {{ $errors->first('roles') }}
            </p>
        @endif
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.btn-group .dropdown-toggle').click(function () {
            console.log("hello");
            $(this).attr("aria-expanded", true);
            $(".btn-group").addClass("open");
        });
    })
</script>