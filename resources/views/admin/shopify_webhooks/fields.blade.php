<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('topic', 'Event*', ['class' => 'control-label']) !!}
        {!! Form::select('topic', Config::get('constants.webhooks'), old('topic'), ['onchange' => 'FormValidation.j($(this).val());', 'class' => 'form-control inpt-focus', 'placeholder' => 'Select an Event', 'required' => '']) !!}
        @if($errors->has('topic'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('format', 'Format*', ['class' => 'control-label']) !!}
        {!! Form::select('format', array('json' => 'JSON'), old('format'), ['class' => 'form-control', 'placeholder' => 'Select a Format', 'required' => '']) !!}
        @if($errors->has('format'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('address', 'URL*', ['class' => 'control-label']) !!}
        {!! Form::text('address', env('APP_URL_TUNNEL') . '/webhooks/', ['readonly' => 'true', 'id' => 'address', 'class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('address'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
{!! Form::hidden('prepare_url', env('APP_URL_TUNNEL') . '/webhooks/', ['id' => 'prepare_url']) !!}