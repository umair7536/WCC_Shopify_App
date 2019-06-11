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
                        {!! Form::label('product_id', 'Customer', ['class' => 'control-label']) !!}
                        <select name="customer_id" id="customer_id" class="customer_id form-control"></select>
                    </div>
                    <div class="form-group col-md-6">
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
                    <div class="form-group col-md-12">
                        {!! Form::label('product_id', 'Services*', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <select id="product_id" class="form-control select2">
                                <option value="">Select Service</option>
                                @foreach($products as $product)
                                    @if($product['slug'] == 'all')
                                        @continue
                                    @endif
                                    <option value="<?php echo $product['id'] ?>" data-name="<?php echo $product['name'] ?>" data-price="<?php echo $product['price'] ?>" data-id="<?php echo $product['id'] ?>"><?php echo $product['name'] ?></option>
                                @endforeach
                            </select>
                            <span class="input-group-btn"><button class="btn blue" type="button" onclick="FormValidation.addRow();"><i class="fa fa-plus"></i>&nbsp;Add</button></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('total_products', 'Total Services*', ['class' => 'control-label']) !!}
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
                            <th>Service Name</th>
                            <th>Price</th>
                            <th width="10%">Action</th>
                        </tr>
                        </thead>
                        @php($counter = 0)
                        @if($relationships)
                            @foreach($relationships as $relationship)
                                @if(array_key_exists($relationship->product_id, $ticket_products))
                                    @php( $counter = $counter + 1)
                                    <tr id="singleRow{{ $counter }}">
                                        <td>
                                            <input type="hidden" value="{{ $ticket_products[$relationship->product_id]->id }}" id="productID{{ $counter }}" name="product_id[{{ $counter }}]" />
                                            <span id="productText{{ $counter }}">&nbsp;&nbsp;&nbsp;{{ $ticket_products[$relationship->product_id]->name }}</span>
                                        </td>
                                        <td>
                                            <input type="hidden" class="productPriceValue" value="{{ $ticket_products[$relationship->product_id]->price }}" id="productPriceValue{{ $counter }}" name="product_price[{{ $counter }}]" />
                                            <span id="productPrice{{ $counter }}">{{ $ticket_products[$relationship->product_id]->price }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRow('{{ $counter }}')">
                                                <i class="fa fa-trash"></i>&nbsp;Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endif
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