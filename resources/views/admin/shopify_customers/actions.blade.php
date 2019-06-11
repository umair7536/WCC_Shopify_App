@if(Gate::allows('shopify_customers_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.shopify_customers.edit',[$shopify_customer->id]) }}"
       data-target="#ajax_shopify_customers" data-toggle="modal">@lang('global.app_edit')</a>
@endif
{{--@if(Gate::allows('shopify_customers_destroy'))--}}
    {{--{!! Form::open(array(--}}
        {{--'style' => 'display: inline-block;',--}}
        {{--'method' => 'DELETE',--}}
        {{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
        {{--'route' => ['admin.shopify_customers.destroy', $shopify_customer->id])) !!}--}}
    {{--{!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}--}}
    {{--{!! Form::close() !!}--}}
{{--@endif--}}
<a class="btn btn-xs btn-warning" href="{{ route('admin.shopify_customers.detail',[$shopify_customer->id]) }}" data-target="#ajax_shopify_customers_detail" data-toggle="modal">View</a>