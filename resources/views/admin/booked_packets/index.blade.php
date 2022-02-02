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
    <h1 class="page-title">@lang('global.booked_packets.title')</h1>
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
            <!-- <div class="actions">
                @if(Gate::allows('booked_packets_create'))
                    <a class="btn btn-success" href="{{ route('admin.booked_packets.create') }}">@lang('global.app_add_new')</a>
                    <a class="btn btn-success" href="{{ route('admin.booked_packets.sync_status') }}" data-toggle="modal">Sync Packet Statuses</a>
                @endif
            </div> -->
        </div>
        <div class="portlet-body">
            <div class="note note-warning">
                <h4 class="block">Important</h4>
                <p>If clicking on "Print Slip" doesn't download the slip, copy "Print Slip" URL and open it in a new tab to download.</p>
            </div>
            <div class="table-container">
                <div class="table-actions-wrapper">
                    <span> </span>
                    <select class="table-group-action-input form-control input-inline input-small input-sm">
                        <option value="">Bulk Action</option>
                        <option value="cancel">Cancel Booked Packets</option>
                        <option value="fulfill">Mark Fulfill Packets</option>
                        <!-- <option value="loadsheet">Generate Load Sheet</option> -->
                    </select>
                    <button class="btn btn-sm red table-group-action-submit">
                        <i class="fa fa-check"></i> Submit
                    </button>
                </div>
                <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                    <thead>
                    <tr role="row" class="heading">
                        <th width="3%">
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" class="group-checkable" data-set="#sample_2 .checkboxes"/>
                                <span></span>
                            </label>
                        </th>
                        <th>@lang('global.booked_packets.fields.status')</th>
                        <th>@lang('global.booked_packets.fields.order_id')</th>
                        <th>@lang('global.booked_packets.fields.shipment_type_id')</th>
                        <th>@lang('global.booked_packets.fields.cn_number')</th>
{{--                        <th>@lang('global.booked_packets.fields.origin_city')</th>--}}
                        <th>@lang('global.booked_packets.fields.destination_city')</th>
{{--                        <th>@lang('global.booked_packets.fields.shipper_name')</th>--}}
{{--                        <th>@lang('global.booked_packets.fields.consignee_name')</th>--}}
                        <th>@lang('global.booked_packets.fields.consignee_phone')</th>
{{--                        <th>@lang('global.booked_packets.fields.consignee_email')</th>--}}
                        <th>@lang('global.booked_packets.fields.booking_date')</th>
                        <th>@lang('global.booked_packets.fields.invoice_number')</th>
                        <th>@lang('global.booked_packets.fields.invoice_date')</th>
                        <th>@lang('global.booked_packets.fields.collect_amount')</th>
                        <th width="17%">@lang('global.booked_packets.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            {!! Form::select('status', $status, null, ['class' => 'form-control form-filter input-sm', 'placeholder' => 'Select a Status']) !!}
                        </td>
                        <td>
                            {!! Form::text('order_id', null, ['class' => 'form-control form-filter input-sm']) !!}
                        </td>
                        <td>
                            {!! Form::select('shipment_type_id', $shipment_type, null, ['class' => 'form-control form-filter input-sm', 'placeholder' => 'Select a Shipment Type']) !!}
                        </td>
                        <td>
                            {!! Form::text('cn_number', null, ['class' => 'form-control form-filter input-sm']) !!}
                        </td>
{{--                        <td>--}}
{{--                            {!! Form::select('origin_city', $wcc_cities, null, ['class' => 'form-control form-filter input-sm', 'placeholder' => 'Select a Shipment Type']) !!}--}}
{{--                        </td>--}}
                        <td>
                            {!! Form::select('destination_city', $wcc_cities, null, ['class' => 'form-control form-filter input-sm', 'placeholder' => 'Select a Shipment City']) !!}
                        </td>
{{--                        <td>--}}
{{--                            {!! Form::text('shipper_name', null, ['class' => 'form-control form-filter input-sm']) !!}--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            {!! Form::text('consignee_name', null, ['class' => 'form-control form-filter input-sm']) !!}--}}
{{--                        </td>--}}
                        <td>
                            {!! Form::text('consignee_phone', null, ['class' => 'form-control form-filter input-sm']) !!}
                        </td>
{{--                        <td>--}}
{{--                            {!! Form::text('consignee_email', null, ['class' => 'form-control form-filter input-sm']) !!}--}}
{{--                        </td>--}}
                        <td>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="booking_date_from" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm booking_date_from" placeholder="From">
                            </div>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="booking_date_to" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm booking_date_to" placeholder="To" >
                            </div>
                        </td>
                        <td>
                            {!! Form::text('invoice_number', null, ['class' => 'form-control form-filter input-sm']) !!}
                        </td>
                        <td>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="invoice_date_from" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm invoice_date_from" placeholder="From">
                            </div>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="invoice_date_to" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm invoice_date_to" placeholder="To" >
                            </div>
                        </td>
                        <td>
                            {!! Form::text('collect_amount', null, ['class' => 'form-control form-filter input-sm']) !!}
                        </td>
                        <td>
                            <div class="margin-bottom-5">
                                <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
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
    <div class="modal fade" id="ajax_booked_packets" role="basic" aria-hidden="true" style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
    <!--Detail View model Start-->
    <div class="modal fade" id="ajax_booked_packets_detail" role="basic" aria-hidden="true"  style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Detail View model End-->
    <!--Detail View model Start-->
    <div class="modal fade" id="ajax_booked_packets_fulfill" role="basic" aria-hidden="true"  style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Detail View model End-->
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/booked_packets/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
@endsection