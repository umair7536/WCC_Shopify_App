<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_edit')</h4>
    </div>
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($leopards_setting, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.leopards_settings.update', $leopards_setting->id]]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
                @include('partials.messages')
                <!-- Ends Form Validation Messages -->

                @include('admin.leopards_settings.edit_fields')
            </div>
            <div>
                {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<script src="{{ url('js/admin/leopards_settings/fields.js') }}" type="text/javascript"></script>