<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_edit')</h4>
    </div>
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($shopify_customer, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.shopify_customers.update', $shopify_customer->id]]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
            @include('partials.messages')
            <!-- Ends Form Validation Messages -->

                @include('admin.shopify_customers.editfields')
            </div>
            <div>
                {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<script src="{{ url('js/admin/shopify_customers/fields.js') }}" type="text/javascript"></script>