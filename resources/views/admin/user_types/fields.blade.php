<div class="row">
<div class="form-group col-md-6">
    {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
    {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '']) !!}
    @if($errors->has('name'))
        <p class="help-block">
            {{ $errors->first('name') }}
        </p>
    @endif
</div>
<div class="form-group col-md-6">
    {!! Form::label('type', 'Type*', ['class' => 'control-label']) !!}
    {!! Form::select('type',['application-user'=>'Application User','team-member'=>'Team Member'],old('type'),['class' => 'form-control select2', 'placeholder' => 'Please Select Type']) !!}
    @if($errors->has('type'))
        <p class="help-block">
            {{ $errors->first('type') }}
        </p>
    @endif
</div>
</div>
<div class="clearfix"></div>