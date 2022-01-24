@extends('layouts.app')
@php(
$service_requests = \App\Models\Tickets::where([
    'account_id' => \Illuminate\Support\Facades\Auth::User()->account_id
])->count()
)
@php(
$products = \App\Models\ShopifyProducts::where([
    'account_id' => \Illuminate\Support\Facades\Auth::User()->account_id
])->count()
)

@php(
$customers = \App\Models\ShopifyCustomers::where([
    'account_id' => \Illuminate\Support\Facades\Auth::User()->account_id
])->count()
)
@php(
$orders = \App\Models\ShopifyOrders::where([
    'account_id' => \Illuminate\Support\Facades\Auth::User()->account_id
])->count()
)
@php(
$booked_packets_live = \App\Models\BookedPackets::where([
    'account_id' => \Illuminate\Support\Facades\Auth::User()->account_id,
    'booking_type' => 2
])->count()
)
@php(
$booked_packets_test = \App\Models\BookedPackets::where([
    'account_id' => \Illuminate\Support\Facades\Auth::User()->account_id,
    'booking_type' => 1
])->count()
)
@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-table/bootstrap-table.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
@stop

@section('content')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title"> Dashboard</h1>
    <!-- END PAGE TITLE-->

    @if(
        Gate::allows('tickets_manage')
    )
        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="actions">
                    <a href="{{ route('admin.tickets.create') }}" type="button" class="btn blue btn-sm">Add @lang('global.tickets.single')</a>
                    <a href="{{ route('admin.tickets.index') }}" type="button" class="btn blue btn-sm">All @lang('global.tickets.title')</a>
                </div>
            </div>
        </div>
    @endif

    <div class="row">

        @if(Gate::allows('tickets_manage'))
            <div class="col-md-4">
                <!-- BEGIN WIDGET THUMB -->
                <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                    <h4 class="widget-thumb-heading">@lang('global.tickets.title')</h4>
                    <div class="widget-thumb-wrap">
                        <i class="widget-thumb-icon bg-green icon-support"></i>
                        <div class="widget-thumb-body">
                            <span class="widget-thumb-subtitle">Total</span>
                            <span class="widget-thumb-body-stat" data-counter="counterup" data-value="{{ number_format($service_requests) }}">{{ number_format($service_requests) }}</span>
                        </div>
                    </div>
                </div>
                <!-- END WIDGET THUMB -->
            </div>
        @endif

        @if(Gate::allows('shopify_products_manage'))
            <div class="col-md-4">
                <!-- BEGIN WIDGET THUMB -->
                <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                    <h4 class="widget-thumb-heading">Products</h4>
                    <div class="widget-thumb-wrap">
                        <i class="widget-thumb-icon bg-green icon-layers"></i>
                        <div class="widget-thumb-body">
                            <span class="widget-thumb-subtitle">Total</span>
                            <span class="widget-thumb-body-stat" data-counter="counterup" data-value="{{ number_format($products) }}">{{ number_format($products) }}</span>
                        </div>
                    </div>
                </div>
                <!-- END WIDGET THUMB -->
            </div>
        @endif


        @if(Gate::allows('shopify_customers_manage'))
{{--            <div class="col-md-4">--}}
{{--                <!-- BEGIN WIDGET THUMB -->--}}
{{--                <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">--}}
{{--                    <h4 class="widget-thumb-heading">Customers</h4>--}}
{{--                    <div class="widget-thumb-wrap">--}}
{{--                        <i class="widget-thumb-icon bg-green icon-users"></i>--}}
{{--                        <div class="widget-thumb-body">--}}
{{--                            <span class="widget-thumb-subtitle">Total</span>--}}
{{--                            <span class="widget-thumb-body-stat" data-counter="counterup" data-value="{{ number_format($customers) }}">{{ number_format($customers) }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <!-- END WIDGET THUMB -->--}}
{{--            </div>--}}
        @endif

        @if(Gate::allows('shopify_orders_manage'))
            <div class="col-md-4">
                <!-- BEGIN WIDGET THUMB -->
                <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                    <h4 class="widget-thumb-heading">Orders</h4>
                    <div class="widget-thumb-wrap">
                        <i class="widget-thumb-icon bg-green icon-users"></i>
                        <div class="widget-thumb-body">
                            <span class="widget-thumb-subtitle">Total</span>
                            <span class="widget-thumb-body-stat" data-counter="counterup" data-value="{{ number_format($orders) }}">{{ number_format($orders) }}</span>
                        </div>
                    </div>
                </div>
                <!-- END WIDGET THUMB -->
            </div>
        @endif

        @if(Gate::allows('booked_packets_manage'))
            <div class="col-md-4">
                <!-- BEGIN WIDGET THUMB -->
                <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
                    <h4 class="widget-thumb-heading">Booked Packets</h4>
                    <div class="widget-thumb-wrap">
                        <i class="widget-thumb-icon bg-green icon-users"></i>
                        <div class="widget-thumb-body">
                            <span class="widget-thumb-subtitle">Total</span>
                            <span class="widget-thumb-body-stat" data-counter="counterup" data-value="{{ number_format($booked_packets_live) }}">{{ number_format($booked_packets_live) }}</span>
                        </div>
                    </div>
                </div>
                <!-- END WIDGET THUMB -->
            </div>
        @endif

        @if(Gate::allows('booked_packets_manage'))
            <!-- <div class="col-md-4"> -->
                <!-- BEGIN WIDGET THUMB -->
                <!-- <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered"> -->
                    <!-- <h4 class="widget-thumb-heading">Booked Packets (Test)</h4> -->
                    <!-- <div class="widget-thumb-wrap"> -->
                        <!-- <i class="widget-thumb-icon bg-green icon-users"></i> -->
                        <!-- <div class="widget-thumb-body"> -->
                            <!-- <span class="widget-thumb-subtitle">Total</span> -->
                            <!-- <span class="widget-thumb-body-stat" data-counter="counterup" data-value="{{ number_format($booked_packets_test) }}">{{ number_format($booked_packets_test) }}</span> -->
                        <!-- </div> -->
                    <!-- </div> -->
                <!-- </div> -->
                <!-- END WIDGET THUMB -->
            <!-- </div> -->
        @endif

        <div class="clearfix"></div>

    </div>
@stop

@section('javascript')

@endsection