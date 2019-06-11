@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-table/bootstrap-table.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
@stop

@section('content')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title"> Dashboard</h1>
    <!-- END PAGE TITLE-->

    <div class="row">

        <div class="col-md-3">
            <!-- BEGIN WIDGET THUMB -->
            <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                <h4 class="widget-thumb-heading">Current Balance</h4>
                <div class="widget-thumb-wrap">
                    <i class="widget-thumb-icon bg-green icon-bulb"></i>
                    <div class="widget-thumb-body">
                        <span class="widget-thumb-subtitle">USD</span>
                        <span class="widget-thumb-body-stat" data-counter="counterup" data-value="7,644">7,644</span>
                    </div>
                </div>
            </div>
            <!-- END WIDGET THUMB -->
        </div>

        <div class="col-md-3">
            <!-- BEGIN WIDGET THUMB -->
            <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                <h4 class="widget-thumb-heading">Current Balance</h4>
                <div class="widget-thumb-wrap">
                    <i class="widget-thumb-icon bg-green icon-bulb"></i>
                    <div class="widget-thumb-body">
                        <span class="widget-thumb-subtitle">USD</span>
                        <span class="widget-thumb-body-stat" data-counter="counterup" data-value="7,644">7,644</span>
                    </div>
                </div>
            </div>
            <!-- END WIDGET THUMB -->
        </div>


        <div class="col-md-3">
            <!-- BEGIN WIDGET THUMB -->
            <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                <h4 class="widget-thumb-heading">Current Balance</h4>
                <div class="widget-thumb-wrap">
                    <i class="widget-thumb-icon bg-green icon-bulb"></i>
                    <div class="widget-thumb-body">
                        <span class="widget-thumb-subtitle">USD</span>
                        <span class="widget-thumb-body-stat" data-counter="counterup" data-value="7,644">7,644</span>
                    </div>
                </div>
            </div>
            <!-- END WIDGET THUMB -->
        </div>

        <div class="clearfix"></div>

        {{--<div class="col-lg-2 col-xs-12 col-sm-12">--}}
            {{--<div class="portlet light ">--}}
                {{--<div class="portlet-title">--}}
                    {{--<div class="caption caption-md">--}}
                        {{--<i class="icon-bar-chart font-dark hide"></i>--}}
                        {{--<span class="caption-subject font-dark bold uppercase">System Summary</span>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="portlet-body">--}}
                    {{--<div class="table-scrollable table-scrollable-borderless">--}}
                        {{--<table class="table table-hover table-light">--}}
                            {{--<tbody>--}}
                            {{--@if(Gate::allows('users_manage'))--}}
                                {{--<tr>--}}
                                    {{--<th>Application Users</th>--}}
                                    {{--<td align="right">{{ number_format($report['users']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--@if(Gate::allows('leads_manage') || Gate::allows('leads_view'))--}}
                                {{--<tr>--}}
                                    {{--<th>Leads</th>--}}
                                    {{--<td align="right">{{ number_format($report['leads']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--@if(Gate::allows('appointments_manage') || Gate::allows('appointments_view'))--}}
                                {{--<tr>--}}
                                    {{--<th>Appointments</th>--}}
                                    {{--<td align="right">{{ number_format($report['appointments']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--@if(Gate::allows('cities_manage'))--}}
                                {{--<tr>--}}
                                    {{--<th>Cities</th>--}}
                                    {{--<td align="right">{{ number_format($report['cities']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--@if(Gate::allows('locations_manage'))--}}
                                {{--<tr>--}}
                                    {{--<th>Centres</th>--}}
                                    {{--<td align="right">{{ number_format($report['locations']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--@if(Gate::allows('doctors_manage'))--}}
                                {{--<tr>--}}
                                    {{--<th>Doctors</th>--}}
                                    {{--<td align="right">{{ number_format($report['doctors']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--@if(Gate::allows('doctors_manage'))--}}
                                {{--<tr>--}}
                                    {{--<th>Patients</th>--}}
                                    {{--<td align="right">{{ number_format($report['doctors']) }}</td>--}}
                                {{--</tr>--}}
                            {{--@endif--}}
                            {{--</tbody>--}}
                        {{--</table>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}

        @if(Gate::allows('appointments_manage') || Gate::allows('appointments_view'))
            {{--<div class="col-lg-5 col-xs-12 col-sm-12">--}}
                {{--<div class="portlet light ">--}}
                    {{--<div class="portlet-title">--}}
                        {{--<div class="caption caption-md">--}}
                            {{--<i class="icon-bar-chart font-dark hide"></i>--}}
                            {{--<span class="caption-subject font-dark bold uppercase">Appointments</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<div class="portlet-body">--}}
                        {{--<table data-toggle="table" data-height="299" data-show-columns="true" data-search="true">--}}
                            {{--<thead>--}}
                            {{--<tr>--}}
                                {{--<th data-field="patient" data-align="left" data-sortable="true">Name</th>--}}
                                {{--<th data-field="phone" data-align="left" data-sortable="true">Phone</th>--}}
                                {{--<th data-field="scheduled" data-align="left" data-sortable="true">Scheduled</th>--}}
                                {{--<th data-field="doctor" data-align="left" data-sortable="true">Doctor</th>--}}
                                {{--<th data-field="city" data-align="left" data-sortable="true">City</th>--}}
                                {{--<th data-field="centre" data-align="left" data-sortable="true">Centre</th>--}}
                                {{--<th data-field="created_by" data-align="left" data-sortable="true">Created By</th>--}}
                            {{--</tr>--}}
                            {{--</thead>--}}
                            {{--<tbody>--}}
                                {{--@if($report['recent_appointments'])--}}
                                    {{--@foreach($report['recent_appointments'] as $appointment)--}}
                                        {{--<tr>--}}
                                            {{--<td>{{ ($appointment->patient_name) ? $appointment->patient_name : $appointment->name }}</td>--}}
                                            {{--<td>{{ \App\Helpers\GeneralFunctions::prepareNumber4Call($appointment->phone) }}</td>--}}
                                            {{--<td>{{ ($appointment->scheduled_date) ? \Carbon\Carbon::parse($appointment->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($appointment->scheduled_time, null)->format('h:i A') : '-' }}</td>--}}
                                            {{--<td>{{ $appointment->doctor->name }}</td>--}}
                                            {{--<td>{{ $appointment->city_id ? $appointment->city->name : 'N/A' }}</td>--}}
                                            {{--<td>{{ $appointment->location_id ? $appointment->location->name : 'N/A' }}</td>--}}
                                            {{--<td>{{ $appointment->app_created_by ? $appointment->user->name : 'N/A' }}</td>--}}
                                        {{--</tr>--}}
                                    {{--@endforeach--}}
                                {{--@endif--}}
                            {{--</tbody>--}}
                        {{--</table>--}}
                        {{--<div class="task-footer">--}}
                            {{--<div class="btn-arrow-link pull-right margin-top-10">--}}
                                {{--<a href="{{ route('admin.appointments.index') }}">See All Records</a>--}}
                                {{--<i class="icon-arrow-right"></i>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="clearfix"></div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        @endif

        @if(Gate::allows('leads_manage') || Gate::allows('leads_view'))
            {{--<div class="col-lg-5 col-xs-12 col-sm-12">--}}
                {{--<div class="portlet light ">--}}
                    {{--<div class="portlet-title">--}}
                        {{--<div class="caption caption-md">--}}
                            {{--<i class="icon-bar-chart font-dark hide"></i>--}}
                            {{--<span class="caption-subject font-dark bold uppercase">Leads</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<div class="portlet-body">--}}
                        {{--<table data-toggle="table" data-height="299" data-show-columns="true" data-search="true">--}}
                            {{--<thead>--}}
                            {{--<tr>--}}
                                {{--<th data-field="name" data-align="left" data-sortable="true">Name</th>--}}
                                {{--<th data-field="phone" data-align="left" data-sortable="true">Phone</th>--}}
                                {{--<th data-field="city" data-align="left" data-sortable="true">City</th>--}}
                                {{--<th data-field="service" data-align="left" data-sortable="true">Service</th>--}}
                                {{--<th data-field="created_by" data-align="left" data-sortable="true">Created By</th>--}}
                            {{--</tr>--}}
                            {{--</thead>--}}
                            {{--<tbody>--}}
                                {{--@if($report['recent_leads'])--}}
                                    {{--@foreach($report['recent_leads'] as $lead)--}}
                                        {{--<tr>--}}
                                            {{--<td>{{ $lead->name }}</td>--}}
                                            {{--<td>{{ \App\Helpers\GeneralFunctions::prepareNumber4Call($lead->phone) }}</td>--}}
                                            {{--<td>{{ $lead->city_id ? $lead->city->name : 'N/A' }}</td>--}}
                                            {{--<td>{{ $lead->service_id ? $lead->service->name : 'N/A' }}</td>--}}
                                            {{--<td>{{ $lead->lead_created_by ? $lead->user->name : 'N/A' }}</td>--}}
                                        {{--</tr>--}}
                                    {{--@endforeach--}}
                                {{--@endif--}}
                            {{--</tbody>--}}
                        {{--</table>--}}
                        {{--<div class="task-footer margin-top-10">--}}
                            {{--<div class="btn-arrow-link pull-right">--}}
                                {{--<a href="{{ route('admin.leads.index') }}">See All Records</a>--}}
                                {{--<i class="icon-arrow-right"></i>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="clearfix"></div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        @endif

    </div>
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/moment.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/amcharts/amcharts/amcharts.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/amcharts/amcharts/pie.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->


    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-table/bootstrap-table.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('js/home.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->

    @if(Gate::allows('dashboard_revenue_by_service'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initRevenueByService();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_revenue_by_centre'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initRevenueByCentre();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_revenue_by_service'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyRevenueByService();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_revenue_by_centre'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyRevenueByCentre();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_appointment_by_status'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initAppointmentsByStatus();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_appointment_by_type'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initAppointmentsByType();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_appointment_by_status'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyAppointmentsByStatus();
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_appointment_by_type'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyAppointmentsByType();
            });
        </script>
    @endif
@endsection