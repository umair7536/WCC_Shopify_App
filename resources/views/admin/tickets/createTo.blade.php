<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_create')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::open(['method' => 'POST', 'id' => 'form-validation', 'route' => ['admin.tickets.store']]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
                @include('partials.messages')
                <!-- Ends Form Validation Messages -->

                <div class="row">
                    <div class="form-group col-md-8">
                        {!! Form::label('product_id', 'Customer', ['class' => 'control-label']) !!}
                        <select class="form-control select2">
                            <option value="">Search a Product</option>
                            @foreach($products as $product)
                                <option value="<?php echo $product['product_id'] ?>" data-name="<?php echo $product['title'] ?>" data-id="<?php echo $product['product_id'] ?>"><?php echo $product['title'] ?></option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('ticket_status_id', 'Status*', ['class' => 'control-label']) !!}
                        {!! Form::select('ticket_status_id', $ticket_statuses, old('ticket_status_id') ? old('ticket_status_id') : $ticket_status_id, ['id' => 'ticket_status_id', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has('ticket_status_id'))
                            <p class="help-block">
                                {{ $errors->first('ticket_status_id') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-8">
                        {!! Form::label('product_id', 'Products', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <select id="product_id" class="form-control select2">
                                <option value="">Search a Product</option>
                                @foreach($products as $product)
                                    <option value="<?php echo $product['product_id'] ?>" data-image="<?php echo $product['image_src'] ?>" data-name="<?php echo $product['title'] ?>" data-id="<?php echo $product['product_id'] ?>"><?php echo $product['title'] ?></option>
                                @endforeach
                            </select>
                            <span class="input-group-btn"><button class="btn blue" type="button" onclick="FormValidation.addRow();"><i class="fa fa-plus"></i>&nbsp;Add</button></span>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('total_products', 'Total Products*', ['class' => 'control-label']) !!}
                        {!! Form::number('total_products', old('total_products'), ['id' => 'total_products', 'min' => '1', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has('total_products'))
                            <p class="help-block">
                                {{ $errors->first('total_products') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table_products" class="table table-striped table-bordered table-advance table-hover">
                        <thead>
                        <tr>
                            <th width="10">Product Image</th>
                            <th>Product Name</th>
                            <th width="5%">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
                <div class="clearfix"></div>

                <div class="row">
                    <div class="form-group col-md-12">
                        {!! Form::label('technician_remarks', 'Notes for Technician', ['class' => 'control-label']) !!}
                        {!! Form::textarea('technician_remarks', old('technician_remarks'), ['rows' => '4', 'id' => 'technician_remarks', 'class' => 'form-control', 'placeholder' => '']) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        {!! Form::label('customer_complain', 'Feedback/ Complain from Customer', ['class' => 'control-label']) !!}
                        {!! Form::textarea('customer_complain', old('customer_complain'), ['rows' => '4', 'id' => 'customer_complain', 'class' => 'form-control', 'placeholder' => '']) !!}
                    </div>
                </div>
            </div>
            <div>
                {!! Form::submit(trans('global.app_save'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <script src="{{ url('js/admin/tickets/fields.js') }}" type="text/javascript"></script>
</div>
<input type="hidden" value="0" id="total_productsCount"/>
<table id="rowGenerator" style="display: none;">
    <tr id="singleRowAAA">
        <td id="productImageSrcAAA">
        </td>
        <td>
            <input type="hidden" value="" id="productIDAAA" name="product_id[AAA]" />
            <span id="productTextAAA"></span>
        </td>
        <td>
            <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRow('AAA')">
                <i class="fa fa-trash"></i>&nbsp;Delete
            </button>
        </td>
    </tr>
</table>