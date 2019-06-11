<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 form-group">
        {!! Form::label('title', 'Title*', ['class' => 'control-label']) !!}
        {!! Form::text('title', old('title'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('title'))
            <p class="help-block">
                {{ $errors->first('title') }}
            </p>
        @endif
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 form-group">
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
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 form-group">
        {!! Form::label('parent_id', 'Parent*', ['class' => 'control-label']) !!}
        {!! Form::select('parent_id', $permissions, old('parent_id'), ['class' => 'form-control select2']) !!}
        <span id="parent_id_handler"></span>
        @if($errors->has('parent_id'))
            <span class="help-block">{{ $errors->first('parent_id') }}</span>
        @endif
    </div>
</div>