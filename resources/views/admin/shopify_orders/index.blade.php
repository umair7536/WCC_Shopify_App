@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <style type="text/css">
        #service span.select2-container {
            z-index: 10050;
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
                {{--<div class="table-actions-wrapper">--}}
                    {{--<span> </span>--}}
                    {{--<select class="table-group-action-input form-control input-inline input-small input-sm">--}}
                        {{--<option value="">Select</option>--}}
                        {{--<option value="Delete">Delete</option>--}}
                    {{--</select>--}}
                    {{--<button class="btn btn-sm red table-group-action-submit">--}}
                        {{--<i class="fa fa-check"></i> Submit--}}
                    {{--</button>--}}
                {{--</div>--}}
                <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                    <thead>
                    <tr role="row" class="heading">
                        <th width="3%">
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" class="group-checkable" data-set="#sample_2 .checkboxes"/>
                                <span></span>
                            </label>
                        </th>
                        <th width="10%">@lang('global.shopify_orders.fields.name')</th>
                        <th width="15%">@lang('global.shopify_orders.fields.closed_at')</th>
                        <th>@lang('global.shopify_orders.fields.customer_email')</th>
                        <th width="15%">@lang('global.shopify_orders.fields.financial_status')</th>
                        <th width="15%">@lang('global.shopify_orders.fields.fulfillment_status')</th>
                        <th width="10%">@lang('global.shopify_orders.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="name">
                        </td>
                        <td></td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="customer_email">
                        </td>
                        <td></td>
                        <td></td>
                        <td>
                            <div class="margin-bottom-5">
                                <button class="btn btn-sm green btn-outline filter-submit margin-bottom-5">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button class="btn btn-sm red btn-outline filter-cancel">
                                    <i class="fa fa-times"></i> Reset
                                </button>
                            </div>
                        </td>
                    </tr>
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
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/shopify_orders/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
@endsection