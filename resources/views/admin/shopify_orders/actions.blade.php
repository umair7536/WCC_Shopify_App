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
    <div class="btn-group">
        <button type="button" class="btn btn-success btn-sm"><i class="fa fa-truck"></i> Book</button>
        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-angle-down"></i>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li>
                <a href="{{ route('admin.booked_packets.create',['order_id' => $shopify_order->order_id]) }}">
                    Book in Leopards COD
                </a>
            </li>
        </ul>
    </div>
@endif