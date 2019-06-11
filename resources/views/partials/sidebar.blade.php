@inject('request', 'Illuminate\Http\Request')
<div class="page-sidebar-wrapper">
    <div class="page-sidebar navbar-collapse collapse">
        <ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true"
            data-slide-speed="200" style="padding-top: 20px">
            <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            <li class="sidebar-toggler-wrapper hide">
                <div class="sidebar-toggler">
                    <span></span>
                </div>
            </li>
            <!-- END SIDEBAR TOGGLER BUTTON -->
            <li class="nav-item start {{ $request->segment(2) == 'home' ? 'active' : '' }}">
                <a href="{{ url('/') }}" class="nav-link ">
                    <i class="icon-home"></i>
                    <span class="title">@lang('global.app_dashboard')</span>
                </a>
            </li>

            @if(Gate::allows('permissions_manage') || Gate::allows('roles_manage') || Gate::allows('users_manage') || Gate::allows('user_types_manage'))
                <li class="nav-item start @if(
                    $request->segment(2) == 'permissions' ||
                    $request->segment(2) == 'roles' ||
                    $request->segment(2) == 'users' ||
                    $request->segment(2) == 'user_types'

                ) active open @endif">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="icon-user"></i>
                        <span class="title">@lang('global.user-management.title')</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        @if(Gate::allows('permissions_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'permissions' ? 'active ' : '' }}">
                                <a href="{{ route('admin.permissions.index') }}">
                                    <span class="title">@lang('global.permissions.title')</span>
                                </a>
                            </li>
                        @endif
                        @if(Gate::allows('roles_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'roles' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.roles.index') }}">
                                    <span class="title">@lang('global.roles.title')</span>
                                </a>
                            </li>
                        @endif
                        @if(Gate::allows('users_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'users' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.users.index') }}">
                                    <span class="title">@lang('global.users.title')</span>
                                </a>
                            </li>
                        @endif
                        @if(Gate::allows('user_types_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'user_types' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.user_types.index') }}">
                                    <span class="title">@lang('global.user_types.title')</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endcan


            @if(
                    Gate::allows('tickets_manage')
                )
                <li class="nav-item start @if(
                    $request->segment(2) == 'tickets'
                ) active open @endif">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="icon-support"></i>
                        <span class="title">Services</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        @if(Gate::allows('shopify_products_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'tickets' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.tickets.index') }}">
                                    <span class="title">@lang('global.tickets.title')</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(
                    Gate::allows('shopify_tags_manage')
                    || Gate::allows('shopify_tags_manage')
                    || Gate::allows('shopify_products_manage')
                )
                <li class="nav-item start @if(
                    $request->segment(2) == 'shopify_tags'
                    || $request->segment(2) == 'shopify_products'
                ) active open @endif">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="icon-layers"></i>
                        <span class="title">Products</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        @if(Gate::allows('shopify_tags_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'shopify_tags' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.shopify_tags.index') }}">
                                    <span class="title">@lang('global.shopify_tags.title')</span>
                                </a>
                            </li>
                        @endif
                        @if(Gate::allows('shopify_custom_collections_manage'))
                            {{--<li class="nav-item start {{ $request->segment(2) == 'shopify_custom_collections' ? 'active active-sub' : '' }}">--}}
                                {{--<a href="{{ route('admin.shopify_custom_collections.index') }}">--}}
                                    {{--<span class="title">@lang('global.shopify_custom_collections.title')</span>--}}
                                {{--</a>--}}
                            {{--</li>--}}
                        @endif
                        @if(Gate::allows('shopify_products_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'shopify_products' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.shopify_products.index') }}">
                                    <span class="title">@lang('global.shopify_products.title')</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(
                    Gate::allows('shopify_customers_manage')
                )
                <li class="nav-item start @if(
                    $request->segment(2) == 'shopify_customers'
                ) active open @endif">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="icon-users"></i>
                        <span class="title">Customers</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        @if(Gate::allows('shopify_customers_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'shopify_customers' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.shopify_customers.index') }}">
                                    <span class="title">@lang('global.shopify_customers.title')</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(
                    Gate::allows('settings_manage') ||
                    Gate::allows('shopify_webhooks_manage') ||
                    Gate::allows('ticket_statuses_manage')

                )
                <li class="nav-item start @if(
                $request->segment(2) == 'settings' ||
                $request->segment(2) == 'shopify_webhooks' ||
                $request->segment(2) == 'ticket_statuses'
            ) active open @endif">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="icon-settings"></i>
                        <span class="title">Admin Settings</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        @if(Gate::allows('settings_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'settings' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.settings.index') }}">
                                    <span class="title">@lang('global.settings.title')</span>
                                </a>
                            </li>
                        @endif
                        @if(Gate::allows('shopify_webhooks_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'shopify_webhooks' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.shopify_webhooks.index') }}">
                                    <span class="title">@lang('global.shopify_webhooks.title')</span>
                                </a>
                            </li>
                        @endif
                        @if(Gate::allows('ticket_statuses_manage'))
                            <li class="nav-item start {{ $request->segment(2) == 'ticket_statuses' ? 'active active-sub' : '' }}">
                                <a href="{{ route('admin.ticket_statuses.index') }}">
                                    <span class="title">@lang('global.ticket_statuses.title')</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
        <!-- END SIDEBAR MENU -->
    </div>
    <!-- END SIDEBAR -->
</div>