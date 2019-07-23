<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('title', 'Title*', ['class' => 'control-label']) !!}
        {!! Form::text('title', old('title'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('title'))
            <p class="help-block">
                {{ $errors->first('title') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>