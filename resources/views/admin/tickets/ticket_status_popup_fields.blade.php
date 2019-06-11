{!! Form::hidden('id', old('id'), ['id' => 'ticket']) !!}
<div class="form-group">
    {!! Form::select('ticket_status_id',$ticket_statuses, $ticket_status->id, ['id' => 'ticket_status_id', 'class' => 'form-control ticket_status_id', 'placeholder' => '', 'required' => '']) !!}
</div>
