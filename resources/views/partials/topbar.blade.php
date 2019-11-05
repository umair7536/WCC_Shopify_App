@php(
    $syncProducts = \App\Models\ShopifyJobs::where([
        'type' => 'sync-products',
        'account_id' => Auth::User()->account_id,
    ])->count()
)
@php(
    $syncCustomers = \App\Models\ShopifyJobs::where([
        'type' => 'sync-customers',
        'account_id' => Auth::User()->account_id,
    ])->count()
)
@php(
    $uploadVariants = \App\Models\ShopifyJobs::where([
        'type' => 'upload-variants',
        'account_id' => Auth::User()->account_id,
    ])->count()
)
@php(
    $syncCustomCollections = \App\Models\ShopifyJobs::where([
        'type' => 'sync-custom-collections',
        'account_id' => Auth::User()->account_id,
    ])->count()
)
@php(
    $syncCollects = \App\Models\ShopifyJobs::where([
        'type' => 'sync-collects',
        'account_id' => Auth::User()->account_id,
    ])->count()
)
@php(
    $syncOrders = \App\Models\ShopifyJobs::where([
        'type' => 'sync-orders',
        'account_id' => Auth::User()->account_id,
    ])->count()
)
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="{{ route('admin.home') }}">
                {{--<img src="{{ url('metronic') }}/assets/layouts/layout/img/logo.png" alt="logo" class="logo-default"/>--}}
            </a>
            <div class="menu-toggler sidebar-toggler">
                <span></span>
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse"
           data-target=".navbar-collapse">
            <span></span>
        </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            <ul class="nav navbar-nav pull-right">
                @if($syncProducts || $syncCustomers || $uploadVariants || $syncCustomCollections || $syncCollects || $syncOrders)
                    <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true">
                            <i class="icon-refresh"></i>
                            <span class="badge badge-default"> {{ (($syncProducts + $syncCustomers + $uploadVariants + $syncCustomCollections + $syncCollects + $syncOrders) > 1000) ? number_format(($syncProducts + $syncCustomers + $uploadVariants + $syncCustomCollections + $syncCollects + $syncOrders) / 1000, 2) . 'K' : ($syncProducts + $syncCustomers + $uploadVariants + $syncCustomCollections + $syncCollects + $syncOrders) }} </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="external">
                                <h3><span class="bold">{{ (($syncProducts + $syncCustomers + $uploadVariants + $syncCustomCollections + $syncCollects) > 1000) ? number_format(($syncProducts + $syncCustomers + $uploadVariants + $syncCustomCollections + $syncCollects) / 1000, 2) . 'K' : ($syncProducts  + $syncCustomers + $uploadVariants + $syncCustomCollections + $syncCollects) }} pending</span> processes</h3>
                                {!! Form::open(array(
                                    'method' => 'POST',
                                    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                    'route' => ['admin.clear_processes'])) !!}
                                {!! Form::submit('Clear All', array('class' => 'btn btn-link btn-sm pull-right')) !!}
                                {!! Form::close() !!}
                            </li>
                            <li>
                                <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 150px;">
                                    <ul class="dropdown-menu-list scroller" style="height: 250px; overflow: hidden; width: auto;" data-handle-color="#637283" data-initialized="1">
                                        @if($uploadVariants)
                                            <li>
                                                <a href="javascript:;">
                                                    <span class="time">{{ ($uploadVariants > 1000) ? number_format($uploadVariants / 1000, 2) . 'K' : $uploadVariants }}</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-success">
                                                            <i class="fa fa-upload"></i>
                                                        </span> Upload Variants.
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if($syncProducts)
                                            <li>
                                                <a href="javascript:;">
                                                    <span class="time">{{ ($syncProducts > 1000) ? number_format($syncProducts / 1000, 2) . 'K' : $syncProducts }}</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-download"></i>
                                                        </span> Sync Products.
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if($syncCustomers)
                                            <li>
                                                <a href="javascript:;">
                                                    <span class="time">{{ ($syncCustomers > 1000) ? number_format($syncCustomers / 1000, 2) . 'K' : $syncCustomers }}</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-download"></i>
                                                        </span> Sync Customers.
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if($syncCustomCollections)
                                            <li>
                                                <a href="javascript:;">
                                                    <span class="time">{{ ($syncCustomCollections > 1000) ? number_format($syncCustomCollections / 1000, 2) . 'K' : $syncCustomCollections }}</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-download"></i>
                                                        </span> Sync Custom Collections.
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if($syncCollects)
                                            <li>
                                                <a href="javascript:;">
                                                    <span class="time">{{ ($syncCollects > 1000) ? number_format($syncCollects / 1000, 2) . 'K' : $syncCollects }}</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-download"></i>
                                                        </span> Sync Collects.
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if($syncOrders)
                                            <li>
                                                <a href="javascript:;">
                                                    <span class="time">{{ ($syncOrders > 1000) ? number_format( $syncOrders / 1000, 2) . 'K' :  $syncOrders }}</span>
                                                    <span class="details">
                                                        <span class="label label-sm label-icon label-danger">
                                                            <i class="fa fa-download"></i>
                                                        </span> Sync Orders.
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </li>
                @endif
                <!-- BEGIN USER LOGIN DROPDOWN -->
                <li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"
                       data-close-others="true">
                        <img alt="" class="img-circle"
                             src="{{ url('metronic') }}/assets/layouts/layout/img/avatar.png"/>
                        <span class="username username-hide-on-mobile"> {{ Auth::user()->name }} </span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li>
                            <a href="{{ route('auth.change_password') }}">
                                <i class="icon-key"></i> Change Password
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#logout" onclick="$('#logout').submit();">
                                <i class="icon-key"></i> @lang('global.app_logout')
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- END USER LOGIN DROPDOWN -->
            </ul>
            {!! Form::open(['route' => 'auth.logout', 'style' => 'display:none;', 'id' => 'logout']) !!}
                <button type="submit">@lang('global.logout')</button>
            {!! Form::close() !!}
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>