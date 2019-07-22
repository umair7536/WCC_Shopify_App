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
    <div class="form-group col-md-6">
        {!! Form::label('name', 'Apply Color on Table Row', ['class' => 'control-label']) !!}<br/>
        <label class="mt-checkbox mt-checkbox-outline">
            {!! Form::checkbox('show_color', ($show_colors) ? '1' : '0', old('show_color'), ['id' => 'show_color', 'placeholder' => '']) !!}Yes
            <span></span>
        </label>
        @if($errors->has('show_color'))
            <span class="help-block help-block-error">
                {{ $errors->first('show_color') }}
            </span>
        @endif
    </div>
    <div class="form-group col-md-6 show_color" @if(!$show_colors) style="display: none;" @endif>
        {!! Form::label('color', 'Color', ['class' => 'control-label']) !!}
        {!! Form::color('color', old('color'), ['id' => 'color', 'class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>