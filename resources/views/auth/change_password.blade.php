@extends('layouts.app')

@section('content')
    <!-- BEGIN PAGE TITLE-->
    <!--   <h1 class="page-title">Change Password</h1> -->
    <!-- END PAGE TITLE-->

    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-key font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> Change Password</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::open(['method' => 'PATCH', 'id' => 'form-validation', 'route' => ['auth.change_password']]) !!}
                    <div class="form-body">
                        <div class="alert alert-danger display-hide"><button class="close" data-close="alert"></button> You have some form errors. Please check below. </div>
                        <div class="alert alert-success display-hide"><button class="close" data-close="alert"></button> Your validation is complete, Please wait while we are processing your request! </div>
                        <div class="form-group @if($errors->has('current_password')) has-error @endif">
                            {!! Form::label('current_password', 'Current password*', ['class' => 'control-label']) !!}
                            {!! Form::password('current_password', ['class' => 'form-control', 'placeholder' => '']) !!}
                            @if($errors->has('current_password'))
                                <span class="help-block help-block-error">
                                    {{ $errors->first('current_password') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group @if($errors->has('new_password')) has-error @endif">
                            {!! Form::label('new_password', 'New password*', ['class' => 'control-label']) !!}
                            {!! Form::password('new_password', ['class' => 'form-control', 'placeholder' => '']) !!}
                            @if($errors->has('new_password'))
                                <span class="help-block help-block-error">
                                    {{ $errors->first('new_password') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group @if($errors->has('new_password_confirmation')) has-error @endif">
                            {!! Form::label('new_password_confirmation', 'New password confirmation*', ['class' => 'control-label']) !!}
                            {!! Form::password('new_password_confirmation', ['class' => 'form-control', 'placeholder' => '']) !!}
                            @if($errors->has('new_password_confirmation'))
                                <span class="help-block help-block-error">
                                    {{ $errors->first('new_password_confirmation') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-actions">
                        {!! Form::submit(trans('global.app_save'), ['class' => 'btn btn-success']) !!}
                    </div>
            {!! Form::close() !!}
        </div>
    </div>
    </div>

@stop

@section('javascript')
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/auth/change_password.js') }}" type="text/javascript"></script>
@endsection

