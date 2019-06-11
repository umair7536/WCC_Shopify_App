var FormValidation = function () {
    var e = function () {
        var e = $("#form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {},
            rules: {
                name: {required: !0},
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
                        window.location = route('admin.roles.index');
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

    var selectUnSelectSubGroup = function (selector, group) {
        var permission = group.attr('data-permission');
        var sub_permission = group.attr('data-sub_permission');

        selectUnSelectReset('#', 'sub-allow_' + sub_permission);
        selectUnSelectReset('#', 'sub-noallow_' + sub_permission);

        if (selector == 'selected') {
            // Check if parent checked or not, if not make is checked
            if (!$('.allow_' + permission).is(':checked')) {
                // Reset both buttons
                selectUnSelectReset('#', 'allow_' + permission);
                selectUnSelectReset('#', 'noallow_' + permission);
                // Make Parent Selected
                selectUnSelect('#', 'allow_' + permission, 'noallow_' + permission, true);
            }
            // Make current sub permission checked
            selectUnSelect('#', 'sub-allow_' + sub_permission, 'sub-noallow_' + sub_permission, true);

        } else {
            // Make current sub permission checked
            selectUnSelect('#', 'sub-noallow_' + sub_permission, 'sub-allow_' + sub_permission, false);

            var sub_permissiosn_selected = false;

            $('.sub-allow_' + permission).each(function () {
                var attr = $(this).attr('checked');
                if (typeof attr !== typeof undefined && attr !== false) {
                    sub_permissiosn_selected = true;
                }
            });
        }
    }

    var selectUnSelectGroup = function (selector, group) {
        var permission = group.attr('data-permission');

        // Set Sub Members
        selectUnSelectParent('.', selector, permission, 'sub-allow_' + permission, 'sub-noallow_' + permission, true);
        //selectUnSelectParent('.', selector, 'toggle', 'allow', 'noallow', true);
        // Set Group Head
        selectUnSelectParent('.', selector, permission, 'allow_' + permission, 'noallow_' + permission, false);
    }

    var selectUnSelectGlobal = function (selector) {
        selectUnSelectParent('.', selector, 'toggle', 'allow', 'noallow', true);
    }

    var selectUnSelect = function (id_or_class, allow, noallow, checkFields) {
        $(id_or_class + allow).each(function (ele) {
            $(this).parent().addClass('btn-info');
            $(this).parent().addClass('active');
            if (checkFields) {

                $(this).attr('checked', true);

            }
        });
        $(id_or_class + noallow).each(function (ele) {
            $(this).parent().addClass('btn-default');
        });
    }

    var selectUnSelectReset = function (id_or_class, toogle) {
        $(id_or_class + toogle).each(function (ele) {

            $(this).removeAttr("checked")

            $(this).parent().removeClass('active');
            $(this).parent().removeClass('btn-info');
            $(this).parent().removeClass('btn-default');
        });
    }

    var selectUnSelectParent = function (id_or_class, selector, toogle, allow, noallow, applytoggle) {
        // Apply Reset of Button colors
        if (applytoggle) {
            selectUnSelectReset(id_or_class, toogle);
        }
        if (selector == 'selected') {
            selectUnSelect(id_or_class, allow, noallow, true);
        } else {
            selectUnSelect(id_or_class, noallow, allow, false);
        }
    }


    var checkAll = function (obj) {
        $('.allow_all').not(this).prop('checked', obj.checked);
        if (obj.checked) {
            setElementColor('.label_all');
        } else {
            unsetElementColor('.label_all');
        }

    }
    var checkMyModule = function (obj, selector) {

        $('.sub-' + selector + '').not(this).prop('checked', obj.checked);
        if (obj.checked) {
            setElementColor('.id-' + selector + '');
        } else {
            unsetElementColor('.id-' + selector + '');
        }


    }
    var checkMyParent = function (obj, parent_class, sub_class, value) {
        if (!obj.checked) {
            unsetElementColor('#id-' + value);
        } else {
            setElementColor('#id-' + value);
        }
        // make parent of this module unchecked
        if ($('.' + sub_class + ':checked').length == 0) {

            // $('.' + parent_class + '').prop('checked', false);
            // unsetElementColor('.p-id-' + parent_class);
        } else {
            // make parent of this module checked
            $('.' + parent_class + '').prop('checked', true);
            setElementColor('.p-id-' + parent_class);
        }
    }
    var setElementColor = function (selector) {

        $(selector).addClass('active');
        $(selector).addClass('btn-info');
        $(selector).removeClass('btn-default');
    }
    var unsetElementColor = function (selector) {

        $(selector).removeClass('active');
        $(selector).removeClass('btn-info');
        $(selector).addClass('btn-default');
    }

    return {
        init: function () {
            e()
        },
        selectUnSelectGlobal: selectUnSelectGlobal,
        selectUnSelectGroup: selectUnSelectGroup,
        selectUnSelectSubGroup: selectUnSelectSubGroup,
        checkAll: checkAll,
        checkMyModule: checkMyModule,
        checkMyParent: checkMyParent,
        unsetElementColor: unsetElementColor,
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    // $('#roles-multi').select2({ width: '100%' });
    // $('.select2').select2({ width: '100%' });
});