var FormValidation = function () {
    var e = function () {
        var e = $("#form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
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
                        window.location = route('admin.shopify_customers.index');
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

        $('.password_confirmation').hide();
        $('#password_confirmation').change(function () {
           if($('#password_confirmation:checked').length) {
               $('.password_confirmation').show();
           }  else {
               $('.password_confirmation').hide();
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
                    });
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

    return {
        init: function () {
            e()
        }
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    $('.select2').select2({ width: '100%' });
});