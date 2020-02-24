<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Shipping Information</h4>
    </div>
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($shipping_address, ['method' => 'PUT', 'id' => 'shipping-form-validation', 'route' => ['admin.shopify_orders.shipping_update', $shipping_address->id]]) !!}

                {!! Form::hidden('order_id', $order_id) !!}
                <div class="form-body">
                    <!-- Starts Form Validation Messages -->
                    @include('partials.messages')
                    <!-- Ends Form Validation Messages -->

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('first_name', 'First Name*', ['class' => 'control-label']) !!}
                            {!! Form::text('first_name', old('first_name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('first_name'))
                                <p class="help-block">
                                    {{ $errors->first('first_name') }}
                                </p>
                            @endif
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('last_name', 'Last name*', ['class' => 'control-label']) !!}
                            {!! Form::text('last_name', old('last_name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('last_name'))
                                <p class="help-block">
                                    {{ $errors->first('last_name') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('company', 'Company', ['class' => 'control-label']) !!}
                            {!! Form::text('company', old('company'), ['class' => 'form-control', 'placeholder' => '']) !!}
                            @if($errors->has('company'))
                                <p class="help-block">
                                    {{ $errors->first('company') }}
                                </p>
                            @endif
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('phone', 'Phone', ['class' => 'control-label']) !!}
                            {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('phone'))
                                <p class="help-block">
                                    {{ $errors->first('phone') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('city', 'City*', ['class' => 'control-label']) !!}
                            {!! Form::select('city', $leopards_cities, old('city'), ['class' => 'form-control', 'placeholder' => 'Select a City', 'required' => '']) !!}
                            @if($errors->has('city'))
                                <p class="help-block">
                                    {{ $errors->first('city') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('address1', 'Address*', ['class' => 'control-label']) !!}
                            {!! Form::text('address1', old('address1'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('address1'))
                                <p class="help-block">
                                    {{ $errors->first('address1') }}
                                </p>
                            @endif
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('address2', 'Apartment, suite, etc. (optional)', ['class' => 'control-label']) !!}
                            {!! Form::text('address2', old('address2'), ['class' => 'form-control', 'placeholder' => '']) !!}
                            @if($errors->has('address2'))
                                <p class="help-block">
                                    {{ $errors->first('address2') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div>
                    {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<script type="text/javascript">
    var ShippingFormValidation = function () {
        var e = function () {
            var e = $("#shipping-form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
            e.validate({
                errorElement: "span",
                errorClass: "help-block help-block-error",
                focusInvalid: !1,
                ignore: "",
                messages: {
                },
                rules: {
                    first_name: {required: !0},
                    last_name: {required: !0},
                    phone: {required: !0},
                    city: {required: !0},
                    address1: {required: !0},
                },
                invalidHandler: function (e, t) {
                    i.hide(), r.show(), App.scrollTo(r, -200)
                },
                errorPlacement: function (e, r) {
                    var i = $(r).parent(".input-group");
                    i.size() > 0 ? i.after(e) : r.after(e)
                },
                highlight: function (e) {
                    $(e).closest(".form-group").addClass("has-error")
                },
                unhighlight: function (e) {
                    $(e).closest(".form-group").removeClass("has-error")
                },
                success: function (e) {
                    e.closest(".form-group").removeClass("has-error")
                },
                submitHandler: function (event) {
                    i.show(), r.hide();
                    $("input[type=submit]", e).attr('disabled', true);
                    x(e.attr('action'), e.attr('method'), e.serialize(), function (response) {
                        if (response.status == '1') {
                            var customer_data = response.response;
                            $('#customer_email-' + customer_data.id).html(customer_data.customer_email);
                            $('#destination_city-' + customer_data.id).html(customer_data.destination_city);
                            $('#consignment_address-' + customer_data.id).html(customer_data.consignment_address);
                            r.hide();
                            i.html(response.message);
                            $("input[type=submit]", e).remove();
                        } else {
                            $("input[type=submit]", e).removeAttr('disabled');
                            i.hide();
                            r.html(response.message);
                            r.show();
                        }
                    });
                    return false;
                }
            });
        }

        var x = function (action, method, data, callback) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: action,
                type: method,
                data: data,
                cache: false,
                success: function (response) {
                    if (response.status == '1') {
                        callback({
                            'status': response.status,
                            'message': response.message,
                            'response': response,
                        });
                    } else {
                        callback({
                            'status': response.status,
                            'message': response.message.join('<br/>'),
                            'response': response,
                        });
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    if (xhr.status == '401') {
                        callback({
                            'status': 0,
                            'message': 'You are not authorized to access this resouce',
                        });
                    } else {
                        callback({
                            'status': 0,
                            'message': 'Unable to process your request, please try again later.',
                        });
                    }
                }
            });
        }

        return {
            init: function () {
                e()
            }
        }
    }();
    jQuery(document).ready(function () {
        ShippingFormValidation.init();
        $('.select2').select2({ width: '100%' });
    });
</script>