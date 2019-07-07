@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="{{ url('metronic/assets/pages/css/pricing.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.shopify_billings.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-list font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">@lang('global.shopify_billings.plans')</span>
            </div>
        </div>
        <div class="portlet-body">

            @php(
                $colors = ['blue', 'red', 'green', 'purple', 'yellow', 'grey-salsa']
            )

            <div class="pricing-content-1">
                <div class="row">
                    <div class="col-md-12 margin-bottom-10">
                        Select a plan based on your @lang('global.tickets.title') volume each month. You can compare features of different plans.<br/>
                        Upgrade takes place and change your shipment quota immediately. Downgrade or cancellation and any change of @lang('global.tickets.title') quota are effective starting the next billing cycle.
                    </div>
                    @if($plans)
                        @php(
                            $counter = 0
                        )
                        @foreach($plans as $plan)
                            <div class="col-md-3 margin-bottom-10">
                                <div class="price-column-container border-active">
                                    <div class="price-table-head bg-{{ $colors[$counter] }}">
                                        <h2 class="no-margin">{{ $plan->name }}</h2>
                                    </div>
                                    <div class="arrow-down border-top-{{ $colors[$counter] }}"></div>
                                    <div class="price-table-pricing">
                                        <h3>
                                            @if($plan->price)
                                                <sup class="price-sign">$</sup>
                                            @endif
                                            {{ ($plan->price) ? $plan->price : 'Free' }}
                                        </h3>
                                        <p>per month</p>
                                    </div>
                                    <div class="price-table-content">
                                        <div class="row mobile-padding">
                                            <div class="col-xs-3 text-right mobile-padding">
                                                <i class="icon-support"></i>
                                            </div>
                                            <div class="col-xs-9 text-left mobile-padding">{{ number_format($plan->quota) }} @lang('global.tickets.title') per month</div>
                                        </div>
                                        <div class="row mobile-padding">
                                            <div class="col-xs-3 text-right mobile-padding">
                                                <i class="icon-users"></i>
                                            </div>
                                            <div class="col-xs-9 text-left mobile-padding">24/7 Support</div>
                                        </div>
                                        <div class="row mobile-padding">
                                            <div class="col-xs-3 text-right mobile-padding">
                                                <i class="icon-refresh"></i>
                                            </div>
                                            <div class="col-xs-9 text-left mobile-padding">All features included</div>
                                        </div>
                                    </div>
                                    <div class="arrow-down arrow-grey"></div>
                                    <div class="price-table-footer">
                                        @if($shopify_shop['plan_id'] == $plan->id)
                                            <button type="button" class="btn grey-salsa btn-outline sbold uppercase price-button">Current Plan</button>
                                        @else
                                            @if($ticket_count)
                                            @endif
                                            {!! Form::open(['method' => 'POST', 'id' => 'form-validation', 'route' => ['admin.shopify_billings.store']]) !!}
                                                <input type="hidden" name="plan_id" value="{{ encrypt($plan->id) }}" />
                                                {!! Form::button('Change Plan', ['type' => 'submit', 'class' => 'btn green uppercase price-button']) !!}
                                            {!! Form::close() !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @php($counter++)
                            @if($counter % 4 == 0)
                                <div class="clearfix"></div>
                            @endif
                        @endforeach
                    @else
                        <div class="col-md-3 margin-bottom-10">
                            <div class="price-column-container border-active">
                                <div class="price-table-head bg-{{ $colors[0] }}">
                                    <h2 class="no-margin">Starter</h2>
                                </div>
                                <div class="arrow-down border-top-{{ $colors[0] }}"></div>
                                <div class="price-table-pricing">
                                    <h3>Free</h3>
                                </div>
                                <div class="price-table-content">
                                    <div class="row mobile-padding">
                                        <div class="col-xs-3 text-right mobile-padding">
                                            <i class="icon-support"></i>
                                        </div>
                                        <div class="col-xs-9 text-left mobile-padding">10 @lang('global.tickets.title') per month</div>
                                    </div>
                                    <div class="row mobile-padding">
                                        <div class="col-xs-3 text-right mobile-padding">
                                            <i class="icon-users"></i>
                                        </div>
                                        <div class="col-xs-9 text-left mobile-padding">24/7 Support</div>
                                    </div>
                                    <div class="row mobile-padding">
                                        <div class="col-xs-3 text-right mobile-padding">
                                            <i class="icon-refresh"></i>
                                        </div>
                                        <div class="col-xs-9 text-left mobile-padding">All features included</div>
                                    </div>
                                </div>
                                <div class="arrow-down arrow-grey"></div>
                                <div class="price-table-footer">
                                    <button type="button" class="btn grey-salsa btn-outline sbold uppercase price-button">Current Plan</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- End: Demo Datatable 1 -->
@stop

@section('javascript')

@endsection