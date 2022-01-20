@extends('layouts.app')
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

@stop

@section('content')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title"> Setup Instructions</h1>
    <!-- END PAGE TITLE-->
    <div class="row">

        <div class="col-md-12">
            <div class="portlet light portlet-fit bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-bulb font-green"></i>
                        <span class="caption-subject bold font-green uppercase"> Let's get started by following this guide.</span>
                    </div>
                </div>
                <div class="portlet-body">
                    <p>Before start to follow instructions, please note that two icons are using in below guide.</p>
                    <ul>
                        <li><i class="icon-check font-green-haze"></i> It means these steps are completed.</li>
                        <li><i class="icon-close font-red-intense"></i> It means these steps are not completed yet.</li>
                    </ul>
                    <div class="timeline">
                        <!-- TIMELINE ITEM -->
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <div class="timeline-icon">
                                    <i class="icon-check font-green-haze"></i>
                                </div>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-body-arrow"> </div>
                                <div class="timeline-body-head">
                                    <div class="timeline-body-head-caption">
                                        <span class="timeline-body-alerttitle font-green-haze">Install this App</span>
                                    </div>
                                </div>
                                <div class="timeline-body-content">
                                    <span class="font-grey-cascade"> Thank you for choosing this application.</span>
                                </div>
                            </div>
                        </div>
                        <!-- END TIMELINE ITEM -->
                        <!-- TIMELINE ITEM -->
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <div class="timeline-icon">
                                    @if($wcc_settings['username']->data && $wcc_settings['password']->data)
                                        <i class="icon-check font-green-haze"></i>
                                    @else
                                        <i class="icon-close font-red-intense"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-body-arrow"> </div>
                                <div class="timeline-body-head">
                                    <div class="timeline-body-head-caption">
                                        @if($wcc_settings['username']->data && $wcc_settings['password']->data)
                                            <span class="timeline-body-alerttitle font-green-haze">@lang('global.wcc_settings.title')</span>
                                        @else
                                            <span class="timeline-body-alerttitle font-red-intense">@lang('global.wcc_settings.title')</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="timeline-body-content">
                                    <span class="font-grey-cascade">
                                        <ol>
                                            <li>Click on <a href="{{ route('admin.wcc_settings.index') }}"><span class="title">@lang('global.wcc_settings.title')</span></a> in left dropdown menu of WCC management.</li>
                                            <li>Click on Edit button.</li>
                                            <li>Fill all information by reading instruction provided on that page.</li>
                                            <li>Click on save button.</li>
                                        </ol>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- END TIMELINE ITEM -->
                        <!-- TIMELINE ITEM -->
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <div class="timeline-icon">
                                    @if($booked_packets_live || $booked_packets_test)
                                        <i class="icon-check font-green-haze"></i>
                                    @else
                                        <i class="icon-close font-red-intense"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-body-arrow"> </div>
                                <div class="timeline-body-head">
                                    <div class="timeline-body-head-caption">
                                        @if($booked_packets_live || $booked_packets_test)
                                            <span class="timeline-body-alerttitle font-green-haze">Book a Packet</span>
                                        @else
                                            <span class="timeline-body-alerttitle font-red-intense">Book a Packet</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="timeline-body-content">
                                    <span class="font-grey-cascade">
                                        <ol>
                                            <li>Click on <a href="{{ route('admin.shopify_orders.index') }}">@lang('global.shopify_orders.title')</a> in left menu.</li>
                                            <li>[Optional] Search order that you want to book by using search filters.</li>
                                            <li>In the actions column there will be dropdown named "Book", Click on it then choose "Book in WCC COD".</li>
                                            <li>It will open book a packet form with filled Order Information</li>
                                            <li>[Optional[ Adjust values in fields as per your needs.</li>
                                            <li>click on "Save" button to book this packet. After successful booking of packet you will be redirected back to <a href="{{ route('admin.booked_packets.index') }}">@lang('global.booked_packets.title')</a>.</li>
                                            <li>[Note*] If you are using Test mode, then you can see Booked Packets in <a href="{{ route('admin.booked_packets.api') }}">@lang('global.booked_packets.api_title')</a>.</li>
                                        </ol>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- END TIMELINE ITEM -->
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

    </div>
@stop

@section('javascript')

@endsection