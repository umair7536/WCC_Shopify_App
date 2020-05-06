var TableDatatablesAjax = function () {
    var a = function () {
        $(".date-picker").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".booking_date_from").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".booking_date_to").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".invoice_date_from").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".invoice_date_to").datepicker({rtl: App.isRTL(), autoclose: !0});
        $('.select2').select2({ width: '100%' });
    };

    e = function () {
        var a = new Datatable;
        a.init({
            src: $("#datatable_ajax"), onSuccess: function (a, e) {
            }, onError: function (a) {
            }, onDataLoad: function (a) {
            }, loadingMessage: "Loading...", dataTable: {
                bStateSave: !1,
                cache: !1,
                fnStateSaveParams: function (a, e) {
                    return $("#datatable_ajax tr.filter .form-control").each(function () {
                        e[$(this).attr("name")] = $(this).val()
                    }), e
                },
                fnStateLoadParams: function (a, e) {
                    return $("#datatable_ajax tr.filter .form-control").each(function () {
                        var a = $(this);
                        e[a.attr("name")] && a.val(e[a.attr("name")])
                    }), !0
                },
                lengthMenu: [[15, 25], [15, 25]],
                pageLength: 15,
                "columns": [
                    { "data": "id","bSortable": false },
                    { "data": "status","bSortable": true },
                    { "data": "order_id","bSortable": true },
                    { "data": "shipment_type_id","bSortable": true },
                    { "data": "cn_number","bSortable": true },
                    { "data": "origin_city","bSortable": true },
                    { "data": "destination_city","bSortable": true },
                    { "data": "shipper_name","bSortable": true },
                    { "data": "consignee_name","bSortable": true },
                    { "data": "consignee_phone","bSortable": true },
                    { "data": "consignee_email","bSortable": true },
                    { "data": "booking_date","bSortable": true },
                    { "data": "invoice_number","bSortable": true },
                    { "data": "invoice_date","bSortable": true },
                    { "data": "collect_amount","bSortable": true },
                    { "data": "actions","bSortable": false }
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    url: route('admin.booked_packets.datatable'),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: !0,
                order: [[11, "desc"]]
            }
        }), a.getTableWrapper().on("click", ".table-group-action-submit", function (e) {
            e.preventDefault();
            var t = $(".table-group-action-input", a.getTableWrapper());
            "" != t.val() && a.getSelectedRowsCount() > 0 ? (a.setAjaxParam("customActionType", "group_action"), a.setAjaxParam("customActionName", t.val()), a.setAjaxParam("id", a.getSelectedRows()), a.getDataTable().ajax.reload(), a.clearAjaxParams()) : "" == t.val() ? App.alert({
                type: "danger",
                icon: "warning",
                message: "Please select an action",
                container: a.getTableWrapper(),
                place: "prepend"
            }) : 0 === a.getSelectedRowsCount() && App.alert({
                type: "danger",
                icon: "warning",
                message: "No record selected",
                container: a.getTableWrapper(),
                place: "prepend"
            })
        })
    };

    var setLoader = function (targetId) {
        $('#' + targetId).html('<div class="modal-content">\n' +
            '            <div class="modal-body">\n' +
            '                <span> &nbsp;&nbsp;Loading... </span>\n' +
            '            </div>\n' +
            '        </div>');
    };

    return {
        init: function () {
            a(), e();
        },
        setLoader: setLoader
    }
}();
jQuery(document).ready(function () {
    TableDatatablesAjax.init()
});