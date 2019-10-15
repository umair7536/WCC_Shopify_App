@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <style type="text/css">
        #service span.select2-container {
            z-index: 10050;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.leopards_settings.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-list font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">@lang('global.app_list')</span>
            </div>
            <div class="actions">
                @if(Gate::allows('general_settings_edit'))
                    <a class="btn btn-success" href="{{ route('admin.leopards_settings.create') }}"
                       data-target="#ajax_leopards_settings" data-toggle="modal">@lang('global.app_edit')</a>
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="note note-info">
                <h4 class="block">Integrate Leopards COD Account</h4>
                <p>To simply integrate Leopads COD account follow the steps below. </p>
                <ul>
                    <li><a target="_blank" href="http://new.leopardscod.com/login">Login</a> to Leopards COD system.</li>
                    <li>Go to API Settings from menu (If you didn't see this menu contact Leopards COD support to enable this feature).</li>
                    <li>Go to API Management menu.</li>
                    <li>Copy API Key and API Password (If API Key section is an empty, click on 'Generate Key' button to got API key).</li>
                    <li>Put API Key and API Passwords in this system via editing each setting.</li>
                </ul>
            </div>
            <div class="table-scrollable">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th width="5%"> # </th>
                        <th width="15%"> Name </th>
                        <th> Value </th>
                    </tr>
                    </thead>
                    @if($leopards_settings)
                        @php($sr = 1)
                        <tbody>
                            @foreach($leopards_settings as $leopards_setting)
                                <tr>
                                    <td>{{ $sr }}</td>
                                    <td>{{ $leopards_setting->name }}</td>
                                    @if($leopards_setting->slug == 'password' && $leopards_setting->data)
                                        <td>{{ '****************' }}</td>
                                    @else
                                        <td>{{ ($leopards_setting->slug == 'mode') ? ($leopards_setting->data ? 'Test Mode' : 'Production') : $leopards_setting->data }}</td>
                                    @endif
                                </tr>
                                @php($sr++)
                            @endforeach
                        </tbody>
                    @else
                        <tbody>
                            <tr>
                                <td colspan="3"> No Record Found. </td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <!-- End: Demo Datatable 1 -->
    <!--Add New View model Start-->
    <div class="modal fade" id="ajax_leopards_settings" role="basic" aria-hidden="true"
         style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/leopards_settings/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
@endsection