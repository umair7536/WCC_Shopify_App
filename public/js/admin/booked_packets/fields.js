var BookedPacketValidation = function () {
    var e = function () {
        var e = $("#booked-packet-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
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
                email: {required: !0, email: !0},
                password: {required: '#password:visible'}
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
                        r.hide();
                        i.html(response.message);
                        if(response.test_mode == '1') {
                            window.location = route('admin.booked_packets.api');
                        } else {
                            window.location = route('admin.booked_packets.index');
                        }
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
        $('.form-control.inpt-focus').focus();

        // $('.date-picker').datepicker({
        //     autoclose: true,
        //     format: "yyyy-mm-dd"
        // });
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
                    callback(response);
                } else {
                    callback({
                        'status': response.status,
                        'message': response.message.join('<br/>'),
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
    
    var calculateVol = function () {
        if (
            $('#vol_weight_w').val() != '' && $('#vol_weight_w').val() != 0 &&
            $('#vol_weight_h').val() != '' && $('#vol_weight_h').val() != 0 &&
            $('#vol_weight_l').val() != '' && $('#vol_weight_l').val() != 0
        )
        {
            var volume = (
                parseInt($('#vol_weight_w').val()) *
                parseInt($('#vol_weight_h').val()) *
                parseInt($('#vol_weight_l').val())
            ) / 5000;
            $('#volumetric_dimensions_calculated').val(volume + ' (grams)');
        } else {
            console.log('N?A is added');
            $('#volumetric_dimensions_calculated').val('N/A');
        }
    }

    var changeShipper = function (shipper_value) {
        if(shipper_value == 'self') {
            $('#shipper_name').val($('#company_name_eng').val());
            $('#shipper_address').html($('#company_address1_eng').val());
            $('#shipper_phone').val($('#company_phone').val());
            $('#shipper_email').val($('#company_email').val());
            $('#origin_city').val($('#company_origin_city').val());
            $('#origin_city').trigger('change');
        } else if(shipper_value == 'other') {
            $('#shipper_name').val('');
            $('#shipper_address').html('');
            $('#shipper_phone').val('');
            $('#shipper_email').val('');
            $('#origin_city').val('');
            $('#origin_city').trigger('change');
        } else {
            $('#shipper_name').val('');
            $('#shipper_address').html('');
            $('#shipper_phone').val('');
            $('#shipper_email').val('');
            $('#origin_city').val('');
            $('#origin_city').trigger('change');
        }
    }

    return {
        init: function () {
            e(), calculateVol(), changeShipper($('#shipper_id').val());
        },
        calculateVol: calculateVol,
        changeShipper: changeShipper,
    }
}();
jQuery(document).ready(function () {
    BookedPacketValidation.init();
    // $('.select2').select2({ width: '100%' });
    $('#origin_city').select2({ width: '100%' });
    $('#destination_city').select2({ width: '100%' });
});