<div class="modal-header">
    <button type="button" id="closeBtn" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Update Lead Status</h4>
</div>
{!! Form::model($ticket, ['method' => 'PUT', 'id' => 'status-validation', 'route' => ['admin.tickets.update', $ticket->id]]) !!}
<div class="modal-body">
    <div class="form-body">

        <div class="alert alert-danger display-hide">
            <button class="close" data-close="alert"></button>
            Please check below.
        </div>

        <div class="alert alert-success display-hide">
            <button class="close" data-close="alert"></button>
            Form is being submit!
        </div>

        @include('admin.tickets.ticket_status_popup_fields')

    </div>
</div>
<div class="modal-footer" id="modal-footer">
    {!! Form::submit(trans('global.app_save'), ['' => 'ticket_status_btn', 'class' => 'btn btn-success']) !!}
</div>
{!! Form::close() !!}
<script src="{{ url('js/admin/tickets/ticket_status.js') }}" type="text/javascript"></script>
