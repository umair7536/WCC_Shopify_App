@if(Gate::allows('shopify_products_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.shopify_products.edit',[$shopify_product->id]) }}"
       data-target="#ajax_shopify_products" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('shopify_products_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.shopify_products.destroy', $shopify_product->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
<a class="btn btn-xs btn-warning" href="{{ route('admin.shopify_products.detail',[$shopify_product->id]) }}" data-target="#ajax_shopify_products_detail" data-toggle="modal">View</a>