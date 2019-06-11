@if($shopify_product->image_src)
    <image src="{{ $shopify_product->image_src }}" alt="{{ $shopify_product->title }}" height="48" />
@endif