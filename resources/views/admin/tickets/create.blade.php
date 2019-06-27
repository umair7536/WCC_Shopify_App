@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        .city span.select2-container {
            z-index: 10050;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.tickets.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-plus font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> @lang('global.app_create')</span>
            </div>
            <div class="actions">
                <a href="{{ route('admin.tickets.index') }}" class="btn dark pull-right">@lang('global.app_back')</a>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::open(['method' => 'POST', 'id' => 'form-validation', 'route' => ['admin.tickets.store']]) !!}

                <div class="form-body col-md-8">
                    <!-- Starts Form Validation Messages -->
                    @include('partials.messages')
                    <!-- Ends Form Validation Messages -->

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('product_id', 'Customer', ['class' => 'control-label']) !!}
                            <select name="customer_id" id="customer_id" class="customer_id form-control"></select>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('ticket_status_id', 'Status*', ['class' => 'control-label']) !!}
                            {!! Form::select('ticket_status_id', $ticket_statuses, old('ticket_status_id') ? old('ticket_status_id') : $ticket_status_id, ['id' => 'ticket_status_id', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('ticket_status_id'))
                                <p class="help-block">
                                    {{ $errors->first('ticket_status_id') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <div class="mt-checkbox-inline">
                                <label class="mt-checkbox">
                                    <input name="customer_confirmation" type="checkbox" id="customer_confirmation" value="1"> Register New Customer
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row customer_confirmation">
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
                    <div class="row customer_confirmation">
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
                </div>

                <div class="col-md-4">
                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('technician_remarks', 'Notes for Technician', ['class' => 'control-label']) !!}
                            {!! Form::textarea('technician_remarks', old('technician_remarks'), ['rows' => '9', 'id' => 'technician_remarks', 'class' => 'form-control', 'placeholder' => '']) !!}
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="row">
                    <div class="form-group col-md-8">
                        {!! Form::label('product_id', 'Products', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <select name="product_id" id="product_id" class="product_id form-control"></select>
                            <span class="input-group-btn"><button class="btn blue" type="button" onclick="FormValidation.addRow();"><i class="fa fa-plus"></i>&nbsp;Add</button></span>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('total_products', 'Total Products*', ['class' => 'control-label']) !!}
                        {!! Form::number('total_products', old('total_products'), ['id' => 'total_products', 'min' => '1', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has('total_products'))
                            <p class="help-block">
                                {{ $errors->first('total_products') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="form-body col-md-12">
                    <div class="table-responsive">
                        <table id="table_products" class="table table-striped table-bordered table-advance table-hover">
                            <thead>
                            <tr>
                                <th width="10%">Image</th>
                                <th>Name</th>
                                <th>Serial Number</th>
                                <th>Customer Feedback</th>
                                <th width="5%">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="form-actions">
                    {!! Form::submit(trans('global.app_save'), ['class' => 'btn btn-success']) !!}
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <input type="hidden" value="0" id="total_productsCount"/>
    <table id="rowGenerator" style="display: none;">
        <tr id="singleRowAAA">
            <td id="productImageSrcAAA"></td>
            <td>
                <input type="hidden" value="" id="productIDAAA" name="product_id[AAA]" />
                <input type="hidden" value="" id="variantIDAAA" name="variant_id[AAA]" />
                <span id="productTextAAA"></span>
            </td>
            <td>
                <input type="text" id="serialNumberAAA" class="form-control" name="serial_number[AAA]" placeholder="Serial Number" />
            </td>
            <td>
                <textarea id="customerFeedbackAAA" class="form-control" name="customer_feedback[AAA]" rows="2"></textarea>
            </td>
            <td>
                <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRow('AAA')">
                    <i class="fa fa-trash"></i>&nbsp;Delete
                </button>
            </td>
        </tr>
    </table>
    <!-- End: Demo Datatable 1 -->
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
    <script src="{{ url('js/admin/tickets/fields.js') }}" type="text/javascript"></script>
@endsection