<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th>ID</th>
            <td>{{ $shopify_product->product_id }}</td>
            <th>Title</th>
            <td>{{ $shopify_product->title }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ $shopify_product->product_type }}</td>
            <th>Vendor</th>
            <td>{{ $shopify_product->vendor }}</td>
        </tr>
        <tr>
            <th>Created</th>
            <td>{{ $shopify_product->created_at }}</td>
            <th>PUblished</th>
            <td>{{ $shopify_product->published_at }}</td>
        </tr>
        </tbody>
    </table>
</div>
{{--<div class="col-md-11">--}}
    {{--<div class="col-md-12">--}}
        {{--<div class="box-header ui-sortable-handle" style="cursor: move;">--}}
            {{--<h4 class="box-title">Variants</h4>--}}
        {{--</div>--}}
    {{--</div>--}}
{{--</div>--}}
{{--<div class="row">--}}
    {{--<div class="col-md-11">--}}
        {{--<div class="col-md-12">--}}
            {{--<div class="portlet-body">--}}
                {{--@if($shopify_product->shopify_product_variants)--}}
                    {{--@foreach($shopify_product->shopify_product_variants as $shopify_product_variant)--}}
                        {{--<table class="table table-striped">--}}
                            {{--<tbody>--}}
                            {{--<tr>--}}
                                {{--<th>ID</th>--}}
                                {{--<td>{{ $shopify_product->product_id }}</td>--}}
                                {{--<th>Title</th>--}}
                                {{--<td>{{ $shopify_product->title }}</td>--}}
                            {{--</tr>--}}
                            {{--<tr>--}}
                                {{--<th>Type</th>--}}
                                {{--<td>{{ $shopify_product->product_type }}</td>--}}
                                {{--<th>Vendor</th>--}}
                                {{--<td>{{ $shopify_product->vendor }}</td>--}}
                            {{--</tr>--}}
                            {{--<tr>--}}
                                {{--<th>Created</th>--}}
                                {{--<td>{{ $shopify_product->created_at }}</td>--}}
                                {{--<th>PUblished</th>--}}
                                {{--<td>{{ $shopify_product->published_at }}</td>--}}
                            {{--</tr>--}}
                            {{--</tbody>--}}
                        {{--</table>--}}
                    {{--@endforeach--}}
                {{--@endif--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
{{--</div>--}}