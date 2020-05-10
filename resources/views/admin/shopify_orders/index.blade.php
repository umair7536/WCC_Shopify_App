@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
    <style type="text/css">
        #service span.select2-container {
            z-index: 10050;
        }
        .form-group .multiselect-native-select div.btn-group {
            width: 110px !important;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.shopify_orders.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-list font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">@lang('global.app_list')</span>
            </div>
            <div class="actions">
                @if(Gate::allows('shopify_orders_create'))
                    <a class="btn btn-success" href="{{ route('admin.shopify_orders.orders') }}" data-toggle="modal">Sync @lang('global.shopify_orders.title')</a>
                    {{--<a class="btn btn-success" href="{{ route('admin.shopify_orders.create') }}"--}}
                       {{--data-target="#ajax_shopify_orders" data-toggle="modal">@lang('global.app_add_new')</a>--}}
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="table-container">
                <div class="table-actions-wrapper">
                    <span> </span>
                    <select class="table-group-action-input form-control input-inline input-small input-sm">
                        <option value="">Bulk Action</option>
                        @foreach($shipment_types as $shipment_id => $shipment_name)
                            <option value="{{ $shipment_id }}">Book Packets with {{ $shipment_name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm red table-group-action-submit">
                        <i class="fa fa-check"></i> Submit
                    </button>
                </div>
                <table class="table table-checkable" id="datatable_ajax">
                    <thead>
                    <tr role="row" class="filter">
                        <td colspan="11">
                            <div class="form-inline">
                                <div class="form-group">
                                    <div class="input-group input-large date-picker input-daterange" data-date="10/11/2012" data-date-format="mm/dd/yyyy">
                                        <input name="date_from" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm date_from" placeholder="Date From">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input name="date_to" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm date_to" placeholder="Date To" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control form-filter input-sm" placeholder="Order #" name="name">
                                </div>
                                <div class="form-group">
                                    {!! Form::select('fulfillment_status', $fulfillment_status, null, [
                                        'multiple' => 'multiple', 'class' => 'form-control form-filter input-sm mt-multiselect',
                                        'data-label' => 'left', 'data-select-all' => 'true', 'data-width' => '100%',
                                        'data-filter' => 'true', 'id' => 'fulfillment_status'
                                    ]) !!}
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control form-filter input-sm" placeholder="Tags" name="tags">
                                </div>
                                <div class="form-group">
                                    {!! Form::select('destination_city', $leopards_cities, null, [
                                        'multiple' => 'multiple', 'class' => 'form-control form-filter input-sm mt-multiselect',
                                        'data-label' => 'left', 'data-select-all' => 'true', 'data-width' => '100%',
                                        'data-filter' => 'true', 'id' => 'destination_city'
                                    ]) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::select('financial_status', $financial_status, null, [
                                        'multiple' => 'multiple', 'class' => 'form-control form-filter input-sm mt-multiselect',
                                        'data-label' => 'left', 'data-select-all' => 'true', 'data-width' => '100%',
                                        'data-filter' => 'true', 'id' => 'financial_status'
                                    ]) !!}
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-default btn-sm green btn-outline filter-submit"><i class="fa fa-search"></i>&nbsp;Filter</button>
                                    <button type="submit" class="btn btn-default btn-sm red btn-outline filter-cancel"><i class="fa fa-times"></i>&nbsp;Reset</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr role="row" class="heading">
                        <th width="3%">
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" class="group-checkable" data-set="#sample_2 .checkboxes"/>
                                <span></span>
                            </label>
                        </th>
                        <th>@lang('global.shopify_orders.fields.name')</th>
{{--                        <th>@lang('global.shopify_orders.fields.closed_at')</th>--}}
                        <th>@lang('global.shopify_orders.fields.customer_email')</th>
                        <th>@lang('global.shopify_orders.fields.fulfillment_status')</th>
                        <th>@lang('global.shopify_orders.fields.tags')</th>
                        <th>@lang('global.shopify_orders.fields.cn_number')</th>
                        <th>@lang('global.shopify_orders.fields.destination_city')</th>
                        <th>@lang('global.shopify_orders.fields.consignment_address')</th>
                        <th>@lang('global.shopify_orders.fields.financial_status')</th>
                        <th>@lang('global.shopify_orders.fields.total_price')</th>
                        <th width="10%">@lang('global.shopify_orders.fields.actions')</th>
                    </tr>
{{--                    <tr role="row" class="filter">--}}
{{--                        <td></td>--}}
{{--                        <td>--}}
{{--                            <input type="text" class="form-control form-filter input-sm" name="name">--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div class="input-icon input-icon-sm right margin-bottom-5">--}}
{{--                                <i class="fa fa-calendar"></i>--}}
{{--                                <input name="date_from" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm date_from" placeholder="From">--}}
{{--                            </div>--}}
{{--                            <div class="input-icon input-icon-sm right margin-bottom-5">--}}
{{--                                <i class="fa fa-calendar"></i>--}}
{{--                                <input name="date_to" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm date_to" placeholder="To" >--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <input type="text" class="form-control form-filter input-sm" name="customer_email">--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            {!! Form::select('fulfillment_status', $fulfillment_status, null, [--}}
{{--                                    'multiple' => 'multiple', 'class' => 'form-control form-filter input-sm mt-multiselect',--}}
{{--                                    'data-label' => 'left', 'data-select-all' => 'true', 'data-width' => '100%',--}}
{{--                                    'data-filter' => 'true'--}}
{{--                            ]) !!}--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <input type="text" class="form-control form-filter input-sm" name="tags">--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <input type="text" class="form-control form-filter input-sm" name="cn_number">--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            {!! Form::select('destination_city', $leopards_cities, null, [--}}
{{--                                    'multiple' => 'multiple', 'class' => 'form-control form-filter input-sm mt-multiselect',--}}
{{--                                    'data-label' => 'left', 'data-select-all' => 'true', 'data-width' => '100%',--}}
{{--                                    'data-filter' => 'true'--}}
{{--                            ]) !!}--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <input type="text" class="form-control form-filter input-sm" name="consignment_address">--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            {!! Form::select('financial_status', $financial_status, null, [--}}
{{--                                    'multiple' => 'multiple', 'class' => 'form-control form-filter input-sm mt-multiselect',--}}
{{--                                    'data-label' => 'left', 'data-select-all' => 'true', 'data-width' => '100%',--}}
{{--                                    'data-filter' => 'true'--}}
{{--                            ]) !!}--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <input type="text" class="form-control form-filter input-sm" name="total_price">--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div class="margin-bottom-5">--}}
{{--                                <button class="btn btn-sm green btn-outline filter-submit margin-bottom-5">--}}
{{--                                    <i class="fa fa-search"></i> Search--}}
{{--                                </button>--}}
{{--                                <button class="btn btn-sm red btn-outline filter-cancel">--}}
{{--                                    <i class="fa fa-times"></i> Reset--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End: Demo Datatable 1 -->
    <!--Add New View model Start-->
    <div class="modal fade" id="ajax_shopify_orders" role="basic" aria-hidden="true"
         style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
    <!--Add New View model Start-->
    <div class="modal fade" id="ajax_shopify_customers" role="basic" aria-hidden="true"
         style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
    <!--Add New View model Start-->
    <div class="modal fade" id="ajax_shipping_address" role="basic" aria-hidden="true"
         style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
    <!--Add New View model Start-->
    <div class="modal fade" id="ajax_error_logs" role="basic" aria-hidden="true"
         style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/shopify_orders/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
@endsection