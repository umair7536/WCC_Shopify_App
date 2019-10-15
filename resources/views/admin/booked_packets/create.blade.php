@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->

    <style type="text/css">
        #service span.select2-container {
            z-index: 10050;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.booked_packets.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-plus font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> @lang('global.app_create')</span>
            </div>
            <div class="actions">
                <a href="{{ route('admin.booked_packets.index') }}" class="btn dark pull-right">@lang('global.app_back')</a>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::open(['method' => 'POST', 'id' => 'form-validation', 'route' => ['admin.booked_packets.store']]) !!}
                    <div class="form-body">
                        <!-- Starts Form Validation Messages -->
                        @include('partials.messages')
                        <!-- Ends Form Validation Messages -->

                        @include('admin.booked_packets.fields')
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

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
    <script src="{{ url('js/admin/booked_packets/fields.js') }}" type="text/javascript"></script>
@endsection