@if($shopify_plan->slug == 'default')
    @if($shopify_plan->active)
        @if(Gate::allows('shopify_plans_inactive'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.shopify_plans.inactive', $shopify_plan->id])) !!}
            {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
            {!! Form::close() !!}
        @endif
    @else
        @if(Gate::allows('shopify_plans_active'))
            {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'PATCH',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.shopify_plans.active', $shopify_plan->id])) !!}
            {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
            {!! Form::close() !!}
        @endif
    @endif
@endif
@if(Gate::allows('shopify_plans_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.shopify_plans.edit',[$shopify_plan->id]) }}"
       data-target="#ajax_shopify_plans" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if($shopify_plan->slug == 'default')
    @if(Gate::allows('shopify_plans_destroy'))
        {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'DELETE',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.shopify_plans.destroy', $shopify_plan->id])) !!}
        {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
        {!! Form::close() !!}
    @endif
@endif