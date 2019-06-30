<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_edit')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($ticket, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.tickets.update', $ticket->id]]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
            @include('partials.messages')
            <!-- Ends Form Validation Messages -->

                <div class="row">
                    <div class="form-group col-md-6">
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
                    <div class="form-group col-md-6">
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
                            <th width="10%">Image</th>
                            <th>Name</th>
                            <th>Serial Number</th>
                            <th>Customer Feedback</th>
                            <th width="5%">Action</th>
                        </tr>
                        </thead>

                        @if($ticket_products)
                            @php($counter = 0)
                            @foreach($ticket_products as $ticket_product)
                                @php( $counter = $counter + 1)
                                <tr id="singleRow{{ $counter }}">
                                    <td id="productImageSrc{{ $counter }}">
                                        <img src="{{ $ticket_product->image_src }}" width="60" />
                                    </td>
                                    <td>
                                        <input type="hidden" value="{{ $ticket_product->product_id }}" id="productID{{ $counter }}" name="product_id[{{ $counter }}]" />
                                        <span id="productText{{ $counter }}">{{ $ticket_product->title }}</span>
                                    </td>
                                    <td>
                                        <input type="text" id="serialNumber{{ $counter }}" value="{{ $ticket_product->serial_number }}" class="form-control" name="serial_number[{{ $counter }}]" placeholder="Serial Number" />
                                    </td>
                                    <td>
                                        <textarea id="customerFeedback{{ $counter }}" class="form-control" value="{{ $ticket_product->customer_feedback }}" name="customer_feedback[{{ $counter }}]" rows="2">{{ $ticket_product->customer_feedback }}</textarea>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRow('{{ $counter }}')">
                                            <i class="fa fa-trash"></i>&nbsp;Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </div>
                <div class="clearfix"></div>
            </div>
            <div>
                {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
        <script src="{{ url('js/admin/tickets/fields.js') }}" type="text/javascript"></script>
    </div>
</div>
<input type="hidden" value="{{ count($relationships) }}" id="total_productsCount"/>
<table id="rowGenerator" style="display: none;">
    <tr id="singleRowAAA">
        <td id="productImageSrcAAA"></td>
        <td>
            <input type="hidden" value="" id="productIDAAA" name="product_id[AAA]" />
            <span id="productTextAAA"></span>
        </td>
        <td>
            <input type="text" id="serialNumberAAA" class="form-control" name="serial_number[AAA]" placeholder="Serial Number" />
        </td>
        <td>
            <textarea id="customerFeedbackAAA" class="form-control" name="customer_feedback[AAA]" rows="2"></textarea>
        </td>
        <td>
            <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRow('AAA')">
                <i class="fa fa-trash"></i>&nbsp;Delete
            </button>
        </td>
    </tr>
</table>