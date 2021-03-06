@if($shopify_order->active)
    @if(Gate::allows('shopify_orders_inactive'))
        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'PATCH',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.shopify_orders.inactive', $shopify_order->id])) !!}
        {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
        {!! Form::close() !!}
    @endif
@else
    @if(Gate::allows('shopify_orders_active'))
        {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'PATCH',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.shopify_orders.active', $shopify_order->id])) !!}
        {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
        {!! Form::close() !!}
    @endif
@endif
@if(Gate::allows('shopify_orders_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.shopify_orders.edit',[$shopify_order->id]) }}"
       data-target="#ajax_shopify_orders" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('shopify_orders_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.shopify_orders.destroy', $shopify_order->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
@if(Gate::allows('booked_packets_create'))
    <a class="btn btn-xs btn-success margin-bottom-5" href="{{ route('admin.shopify_orders.book_packet',['id' => $shopify_order->id]) }}">
        <i class="fa fa-truck"></i>&nbsp;Auto Book
    </a>
    <a class="btn btn-xs btn-info margin-bottom-5" href="{{ route('admin.shopify_orders.book_manual',['id' => $shopify_order->order_id, 'shop' => $shop->myshopify_domain]) }}" target="_blank">
        <i class="fa fa-gears"></i>&nbsp;Manual Book
    </a>
    <a class="btn btn-xs btn-danger margin-bottom-5" href="{{ route('admin.shopify_orders.error_log',[$shopify_order->order_id]) }}" data-target="#ajax_error_logs" data-toggle="modal">
        <i class="fa fa-eye"></i>&nbsp;Error Log
    </a>
@endif
{{--@if(Gate::allows('shopify_customers_edit') && $shopify_order->customer_id)--}}
{{--    <a class="btn btn-xs btn-warning" href="{{ route('admin.shopify_customers.edit',[$shopify_order->customer_id]) }}" data-target="#ajax_shopify_customers" data-toggle="modal">--}}
{{--        <i class="fa fa-pencil"></i>&nbsp;Edit Customer--}}
{{--    </a>--}}
{{--@endif--}}
@if(Gate::allows('booked_packets_create'))
    @if(isset($shipping_addresses[$shopify_order->order_id]))
        <a class="btn btn-xs btn-warning" href="{{ route('admin.shopify_orders.shipping',['order_id' => $shopify_order->order_id, 'shipping_id' => $shipping_addresses[$shopify_order->order_id]->id]) }}" data-target="#ajax_shipping_address" data-toggle="modal">
            <i class="fa fa-pencil"></i>&nbsp;Edit Customer
        </a>
    @elseif(Gate::allows('shopify_customers_edit') && $shopify_order->customer_id)
        <a class="btn btn-xs btn-warning" href="{{ route('admin.shopify_orders.shipping',['order_id' => $shopify_order->order_id, 'shipping_id' => '', 'customer_id' => $shopify_order->customer_id]) }}" data-target="#ajax_shipping_address" data-toggle="modal">
            <i class="fa fa-pencil"></i>&nbsp;Edit Customer
        </a>
    @endif
@endif