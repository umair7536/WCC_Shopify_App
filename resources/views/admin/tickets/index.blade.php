@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
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
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-list font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">@lang('global.app_list')</span>
            </div>
            <div class="actions">
                @if(Gate::allows('tickets_create'))
                    <a class="btn btn-success btn-to-focus" href="{{ route('admin.tickets.create') }}">@lang('global.app_add_new')</a>
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="table-container">
                <div class="table-actions-wrapper">
                    <span> </span>
                    <select class="table-group-action-input form-control input-inline input-small input-sm">
                        <option value="">Select</option>
                        <option value="Delete">Delete</option>
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
                        <th>@lang('global.tickets.fields.number')</th>
                        <th>@lang('global.tickets.fields.customer_name')</th>
                        <th>@lang('global.tickets.fields.serial_number')</th>
                        <th>@lang('global.tickets.fields.total_products')</th>
                        <th>@lang('global.tickets.fields.ticket_status_id')</th>
                        <th>@lang('global.tickets.fields.created_at')</th>
                        <th width="17%">@lang('global.tickets.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="number">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="customer_name">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="serial_number">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="total_products">
                        </td>
                        <td>
                            {!! Form::select('ticket_status_id',$ticket_statuses, null, ['class' => 'form-control form-filter input-sm']) !!}
                        </td>
                        <td>
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly="" name="date_from" placeholder="From">
                                <span class="input-group-btn">
                                    <button class="btn btn-sm default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly="" name="date_to" placeholder="To">
                                <span class="input-group-btn">
                                    <button class="btn btn-sm default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
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
    <div class="modal fade bs-modal-sm" id="ajax" role="basic" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal -->
    <!--Edit View model Start-->
    <div class="modal fade" id="ajax_tickets" role="basic" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Edit View model End-->
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/tickets/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>

@endsection