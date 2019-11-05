var TableDatatablesAjax = function () {
    var a = function () {
        $(".date-picker").datepicker({rtl: App.isRTL(), autoclose: !0});
        $('.select2').select2({ width: '100%' });
    };

    e = function () {
        var a = new Datatable;
        a.init({
            src: $("#datatable_ajax"), onSuccess: function (a, e) {
            }, onError: function (a) {
            }, onDataLoad: function (a) {
            }, loadingMessage: "Loading...", dataTable: {
                bStateSave: !0,
                cache: !0,
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
                lengthMenu: [[25, 50, 100], [25, 50, 100]],
                pageLength: 25,
                "columns": [
                    { "data": "id","bSortable": false },
                    { "data": "number" },
                    { "data": "customer_name" },
                    { "data": "serial_number" },
                    { "data": "total_products" },
                    { "data": "ticket_status_id" },
                    { "data": "created_at" },
                    { "data": "actions","bSortable": false }
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    url: route('admin.tickets.datatable'),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: !0,
                order: [[6, "desc"]],
                "createdRow": function( row, data, dataIndex ) {
                    // $(row).attr('style', 'background-color: lightgreen !important');
                    if(data.status_id && $('#show_color' + data.status_id).val() != '0') {
                        $(row).children(':nth-child(1)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(2)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(3)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(4)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(5)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(6)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(7)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                        $(row).children(':nth-child(8)').attr('style', 'background-color: ' + $('#color' + data.status_id).val() + ' !important;');
                    }
                },
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

    return {
        init: function () {
            a(), e();
        }
    }
}();
jQuery(document).ready(function () {
    TableDatatablesAjax.init()
});