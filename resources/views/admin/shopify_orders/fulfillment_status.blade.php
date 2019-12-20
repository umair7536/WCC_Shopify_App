@if($shopify_order->fulfillment_status)
    @if($shopify_order->fulfillment_status == 'unfulfilled')
        <span class="label label-warning"> {{ ucfirst($shopify_order->fulfillment_status) }} </span>
    @elseif($shopify_order->fulfillment_status == 'fulfilled')
        <span class="label label-success"> {{ ucfirst($shopify_order->fulfillment_status) }} </span>
    @elseif($shopify_order->fulfillment_status == 'partially_fulfilled')
        <span class="label label-danger"> Partially Fulfilled </span>
    @else
        <span class="label label-default"> {{ ucfirst($shopify_order->fulfillment_status) }} </span>
    @endif
@else
    <span class="label label-default"> Pending </span>
@endif