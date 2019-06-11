var StatusValidation = function () {
    var e = function () {
        var e = $("#status-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {
            },
            rules: {
                ticket_status_id: {required: !0},
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show()
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
            submitHandler: function (e) {
                i.show(), r.hide();
                $('#ticket_status_btn').attr('disabled',true);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: route('admin.tickets.storeticketstatus'),
                    type: "PUT",
                    data: $("#status-validation").serialize(),
                    cache: false,
                    success: function(response) {
                        if(response.status == '1') {
                            $('.alert-success').html("Form is submitted successfully!");
                            $('#modal-footer').remove();
                            $('#ticket' + $('#ticket').val()).html($("#ticket_status_id option:selected").text());
                            setTimeout(function() {
                                $('#closeBtn').click();
                            }, 1000);
                        } else {
                            $('#ticket_status_btn').removeAttr('disabled');
                        }
                    }
                });
                return false;
            }
        });
        // $('.select2').select2({ width: '100%' });
    }
    return {
        init: function () {
            e()
        }
    }
}();
jQuery(document).ready(function () {
    StatusValidation.init()
});