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
                // customer_id: {required: !0},
                customer_id: {
                    required: {
                        depends: function(element) {
                            return !($('#customer_confirmation:checked').length);
                        }
                    }
                },
                ticket_status_id: {required: !0},
                total_products: {required: !0},
                first_name: {required: '#first_name:visible'},
                last_name: {required: '#last_name:visible'},
                email: {required: '#email:visible', email: !0},
                'serial_number[]': {required: !0},
                // phone: {required: '#phone:visible'},
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
                $("#mark_repaired", e).attr('disabled', true);

                x(e.attr('action'), e.attr('method'), e.serialize(), function (response) {
                    if (response.status == '1') {
                        r.hide();
                        i.html(response.message);
                        window.location = route('admin.tickets.index');
                    } else {
                        $("input[type=submit]", e).removeAttr('disabled');
                        $("#mark_repaired", e).removeAttr('disabled');
                        i.hide();
                        r.html(response.message);
                        r.show();
                    }
                });
                return false;
            }
        })
        $('.form-control.inpt-focus').focus();


        $('.customer_confirmation').hide();
        $('#customer_confirmation').change(function () {
            addUsers();
            if($('#customer_confirmation:checked').length) {
                $('.customer_confirmation').show();
            }  else {
                $('.customer_confirmation').hide();
            }
        });

        $('#mark_repaired').click(function () {
            $('#repaired').val(1);
            e.submit();
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

    var total_products = parseInt($('#total_productsCount').val());
    var total_repairs = parseInt($('#total_repairsCount').val());
    var counter = 1000;
    var repair_counter = 1000;

    var addRow = function () {
        if($('#product_id').val() != '') {

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.tickets.get_product_detail'),
                type: 'GET',
                data: {
                    variant_id: $('#product_id').val()
                },
                cache: false,
                success: function (response) {

                    if(response.status == '1') {

                        // Add count on row
                        counter = counter + 1;

                        var singleRow = $('#rowGenerator').html();
                        singleRow = singleRow.replace(/AAA/g, counter);
                        singleRow = singleRow.replace(/BBB/g, '');
                        $('#table_products').append(singleRow);


                        $("#productID" + counter).val(response.product.product_id);
                        $("#variantID" + counter).val(response.product.variant_id);
                        $("#productText" + counter).html(response.product.product_title);
                        $("#productImageSrc" + counter).html("<img src=' " + response.product.product_image + "' height='60' />");
                        $("#productPrice" + counter).html(response.product.product_price);

                        // Increment Total Products count
                        total_products = total_products + 1;
                        $('#total_products').val(total_products);
                        $('#total_productsCount').val(total_products);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {

                }
            });
        }
    }

    var addRepairRow = function () {
        if($('#repair_product_id').val() != '') {

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.tickets.get_product_detail'),
                type: 'GET',
                data: {
                    variant_id: $('#repair_product_id').val()
                },
                cache: false,
                success: function (response) {

                    if(response.status == '1') {

                        // Add count on row
                        repair_counter = repair_counter + 1;

                        var singleRow = $('#rowRepairGenerator').html();
                        singleRow = singleRow.replace(/AAA/g, repair_counter);
                        singleRow = singleRow.replace(/BBB/g, '');
                        $('#table_repairs').append(singleRow);


                        $("#repair_productID" + repair_counter).val(response.product.product_id);
                        $("#repair_variantID" + repair_counter).val(response.product.variant_id);
                        $("#repair_productText" + repair_counter).html(response.product.product_title);
                        $("#repair_productImageSrc" + repair_counter).html("<img src=' " + response.product.product_image + "' height='60' />");
                        $("#repair_productPrice" + repair_counter).html(response.product.product_price);

                        // Increment Total Products count
                        total_repairs = total_repairs + 1;
                        $('#total_repairs').val(total_repairs);
                        $('#total_repairsCount').val(total_repairs);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {

                }
            });
        }
    }

    var calculateProductsTotal = function () {
        var totalPrice = 0;
        $('.productPriceValue').each(function (index, value) {
            totalPrice = totalPrice + parseFloat($(this).val());
        });
        $('#products_price').val(totalPrice);
    }

    var deleteRow = function (id) {
        if(confirm('Are you sure to delete')) {

            $("#singleRow" + id).remove();

            // Decrement Total Products count
            total_products = total_products - 1;
            $('#total_products').val(total_products);
            $('#total_productsCount').val(total_products);
        }
    }

    var deleteRepairRow = function (id) {
        if(confirm('Are you sure to delete')) {

            $("#repair_singleRow" + id).remove();

            // Decrement Total Products count
            total_repairs = total_repairs - 1;
            $('#total_repairs').val(total_repairs);
            $('#total_repairsCount').val(total_repairs);
        }
    }

    return {
        init: function () {
            e();
        },
        addRow: addRow,
        addRepairRow: addRepairRow,
        deleteRow: deleteRow,
        deleteRepairRow: deleteRepairRow,
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();

    $('#product_id').select2({ width: '100%' });

    $("#customer_id").select2({
        placeholder: 'Search a Customer with 2 or more characters',
        ajax: {
            url: route('admin.tickets.get_customer'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name + ' - ' + item.email,
                            id: item.customer_id
                        }
                    }),
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 3,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    $("#product_id").select2({
        placeholder: 'Search a Product with 2 or more characters',
        ajax: {
            url: route('admin.tickets.get_product'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    search_type: 'bookin',
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    }),
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 3,
        templateResult: formatRepo,
        templateSelection: formatProductSelection
    });

    $("#repair_product_id").select2({
        placeholder: 'Search a Product with 2 or more characters',
        ajax: {
            url: route('admin.tickets.get_product'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    search_type: 'repair',
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    }),
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 3,
        templateResult: formatRepo,
        templateSelection: formatRepairProductSelection
    });
});

function formatRepo(item) {
    if (item.loading) {
        return item.text;
    }
    markup = item.text;
    return markup;
}

function formatRepoSelection(item) {
    if (item.id) {
        return item.text + " <button onclick='addUsers()' class='croxcli' style='float: right;border: 0; background: none;padding: 0 0 0;'><i class='fa fa-times' aria-hidden='true'></i></button>";
    } else {
        return 'Search a Customer with 2 or more characters';
    }
}

function addUsers() {
    $('.customer_id').val(null).trigger('change');
};

function formatProductSelection(item) {
    if (item.id) {
        return item.text + " <button onclick='setProducts()' class='croxcli' style='float: right;border: 0; background: none;padding: 0 0 0;'><i class='fa fa-times' aria-hidden='true'></i></button>";
    } else {
        return 'Search a Product with 2 or more characters';
    }
}

function setProducts() {
    $('.product_id').val(null).trigger('change');
};

function formatRepairProductSelection(item) {
    if (item.id) {
        return item.text + " <button onclick='setRepairProducts()' class='croxcli' style='float: right;border: 0; background: none;padding: 0 0 0;'><i class='fa fa-times' aria-hidden='true'></i></button>";
    } else {
        return 'Search a Product with 2 or more characters';
    }
}

function setRepairProducts() {
    $('.repair_product_id').val(null).trigger('change');
};