@extends('layouts.app')
<!-- BEGIN PAGE LEVEL PLUGINS -->
<link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
      type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL PLUGINS -->
<link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
<style type="text/css">
    .city span.select2-container {
        z-index: 10050;
    }
</style>
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.tickets.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-plus font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> @lang('global.app_edit')</span>
            </div>
            <div class="actions">
                <a href="{{ route('admin.tickets.index') }}" class="btn dark pull-right">@lang('global.app_back')</a>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::model($ticket, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.tickets.update', $ticket->id]]) !!}

                    <div class="form-body">
                        <!-- Starts Form Validation Messages -->
                        @include('partials.messages')
                        <!-- Ends Form Validation Messages -->
                    </div>

                    <div class="portlet portlet-fit">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold font-dark uppercase margin-bottom-5">Step 01: Select Customer</span><br/>
                                <span class="caption-helper">Which customer submitted the returned product(s)?</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    {!! Form::label('product_id', 'Customer', ['class' => 'control-label']) !!}
                                    <select name="customer_id" id="customer_id" class="customer_id form-control">
                                        <option selected="selected" value="{{ $shopify_customer->customer_id }}">{{ $shopify_customer->first_name . ' ' . $shopify_customer->last_name . ' - ' . $shopify_customer->email }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('ticket_status_id', 'Status*', ['class' => 'control-label']) !!}
                                    {!! Form::select('ticket_status_id', $ticket_statuses, old('ticket_status_id'), ['id' => 'ticket_status_id', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                    @if($errors->has('ticket_status_id'))
                                        <p class="help-block">
                                            {{ $errors->first('ticket_status_id') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('customer_confirmation', 'Customer Registration', ['class' => 'control-label']) !!}
                                    <div class="mt-checkbox-inline">
                                        <label class="mt-checkbox">
                                            <input name="customer_confirmation" type="checkbox" id="customer_confirmation" value="1"> Register New Customer
                                            <span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="clearfix"></div>

                                <div class="customer_confirmation">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('first_name', 'First Name*', ['class' => 'control-label']) !!}
                                        {!! Form::text('first_name', old('first_name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
                                        @if($errors->has('first_name'))
                                            <p class="help-block">
                                                {{ $errors->first('first_name') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('last_name', 'Last name*', ['class' => 'control-label']) !!}
                                        {!! Form::text('last_name', old('last_name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                        @if($errors->has('last_name'))
                                            <p class="help-block">
                                                {{ $errors->first('last_name') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="customer_confirmation">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('email', 'Email*', ['class' => 'control-label']) !!}
                                        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                        @if($errors->has('email'))
                                            <p class="help-block">
                                                {{ $errors->first('email') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('phone', 'Phone', ['class' => 'control-label']) !!}
                                        {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '']) !!}
                                        @if($errors->has('phone'))
                                            <p class="help-block">
                                                {{ $errors->first('phone') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="clearfix"></div>

                                <div class="form-group col-md-12">
                                    {!! Form::label('technician_remarks', 'Notes for Technician', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('technician_remarks', old('technician_remarks'), ['rows' => '2', 'id' => 'technician_remarks', 'class' => 'form-control', 'placeholder' => '']) !!}
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="portlet portlet-fit">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold font-dark uppercase margin-bottom-5">Step 02: Book in Returns</span><br/>
                                <span class="caption-helper">Which customer submitted the returned product(s)?</span>
                            </div>
                        </div>
                        <div class="portlet-body">
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

                            <div class="clearfix"></div>

                            <div class="form-body col-md-12">
                                <table id="table_products" class="table table-striped table-bordered table-advance table-hover">
                                    <thead>
                                    <tr>
                                        <th width="10%">Image</th>
                                        <th width="40%">Name</th>
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
                                                    <input type="hidden" value="{{ $ticket_product->variant_id }}" id="variantID{{ $counter }}" name="variant_id[{{ $counter }}]" />
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
                        </div>
                    </div>

                    <div class="portlet portlet-fit">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold font-dark uppercase margin-bottom-5">Step 03: Repair Worksheet</span><br/>
                                <span class="caption-helper">What parts are replaced and why?</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="form-group col-md-8">
                                    {!! Form::label('repair_product_id', 'Products', ['class' => 'control-label']) !!}
                                    <div class="input-group">
                                        <select name="repair_product_id" id="repair_product_id" class="repair_product_id form-control"></select>
                                        <span class="input-group-btn"><button class="btn blue" type="button" onclick="FormValidation.addRepairRow();"><i class="fa fa-plus"></i>&nbsp;Add</button></span>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('total_repairs', 'Total Repair Products*', ['class' => 'control-label']) !!}
                                    {!! Form::number('total_repairs', old('total_repairs'), ['id' => 'total_repairs', 'min' => '0', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '']) !!}
                                    @if($errors->has('total_repairs'))
                                        <p class="help-block">
                                            {{ $errors->first('total_repairs') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="clearfix"></div>

                            <div class="form-body col-md-12">
                                <div class="table-responsive">
                                    <table id="table_repairs" class="table table-striped table-bordered table-advance table-hover">
                                        <thead>
                                        <tr>
                                            <th width="10%">Image</th>
                                            <th>Name</th>
                                            <th>Serial Number</th>
                                            <th>Technician Feedback</th>
                                            <th width="5%">Action</th>
                                        </tr>
                                        </thead>

                                        @if($ticket_repairs)
                                            @php($counter = 0)
                                            @foreach($ticket_repairs as $ticket_repair)
                                                @php( $counter = $counter + 1)
                                                <tr id="repair_singleRow{{ $counter }}">
                                                    <td id="repair_productImageSrc{{ $counter }}">
                                                        <img src="{{ $ticket_repair->image_src }}" width="60" />
                                                    </td>
                                                    <td>
                                                        <input type="hidden" value="{{ $ticket_repair->product_id }}" id="repair_productID{{ $counter }}" name="repair_product_id[{{ $counter }}]" />
                                                        <input type="hidden" value="{{ $ticket_repair->variant_id }}" id="repair_variantID{{ $counter }}" name="repair_variant_id[{{ $counter }}]" />
                                                        <span id="repair_productText{{ $counter }}">{{ $ticket_repair->title }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="repair_serialNumber{{ $counter }}" value="{{ $ticket_repair->serial_number }}" class="form-control" name="repair_serial_number[{{ $counter }}]" placeholder="Serial Number" />
                                                    </td>
                                                    <td>
                                                        <textarea id="repair_customerFeedback{{ $counter }}" class="form-control" value="{{ $ticket_repair->customer_feedback }}" name="repair_customer_feedback[{{ $counter }}]" rows="2">{{ $ticket_repair->customer_feedback }}</textarea>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRepairRow('{{ $counter }}')">
                                                            <i class="fa fa-trash"></i>&nbsp;Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
                    </div>
                {!! Form::close() !!}
            </div>

            <input type="hidden" value="{{ count($relationships) }}" id="total_productsCount"/>
            <input type="hidden" value="{{ count($repair_relationships) }}" id="total_repairsCount"/>

            <table id="rowGenerator" style="display: none;">
                <tr id="singleRowAAA">
                    <td id="productImageSrcAAA"></td>
                    <td>
                        <input type="hidden" value="" id="productIDAAA" name="product_id[AAA]" />
                        <input type="hidden" value="" id="variantIDAAA" name="variant_id[AAA]" />
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

            <table id="rowRepairGenerator" style="display: none;">
                <tr id="repair_singleRowAAA">
                    <td id="repair_productImageSrcAAA"></td>
                    <td>
                        <input type="hidden" value="" id="repair_productIDAAA" name="repair_product_id[AAA]" />
                        <input type="hidden" value="" id="repair_variantIDAAA" name="repair_variant_id[AAA]" />
                        <span id="repair_productTextAAA"></span>
                    </td>
                    <td>
                        <input type="text" id="repair_serialNumberAAA" class="form-control" name="repair_serial_number[AAA]" placeholder="Serial Number" />
                    </td>
                    <td>
                        <textarea id="repair_customerFeedbackAAA" class="form-control" name="repair_customer_feedback[AAA]" rows="2"></textarea>
                    </td>
                    <td>
                        <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRepairRow('AAA')">
                            <i class="fa fa-trash"></i>&nbsp;Delete
                        </button>
                    </td>
                </tr>
            </table>

        </div>
    </div>
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
    <script src="{{ url('js/admin/tickets/fields.js') }}" type="text/javascript"></script>
@endsection