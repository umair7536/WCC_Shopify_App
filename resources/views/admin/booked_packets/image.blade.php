@if($shopify_customer->image_src)
    <image src="{{ $shopify_customer->image_src }}" alt="{{ $shopify_customer->title }}" height="48" />
@endif