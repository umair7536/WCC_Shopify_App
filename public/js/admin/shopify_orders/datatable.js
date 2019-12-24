var TableDatatablesAjax = function () {
    var a = function () {
        $(".date_from").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".date_to").datepicker({rtl: App.isRTL(), autoclose: !0});
        $('.select2').select2({ width: '100%' });

        $('.filter-cancel').click(function (e) {
            // Destroy Multiselect
            $(".mt-multiselect").multiselect("clearSelection");
            $('.mt-multiselect').multiselect('destroy');
            // Reset Multiselect
            ms();
        });
    };

    var ms = function() {
        $('.mt-multiselect').each(function(){
            var btn_class = $(this).attr('class');
            var clickable_groups = ($(this).data('clickable-groups')) ? $(this).data('clickable-groups') : false ;
            var collapse_groups = ($(this).data('collapse-groups')) ? $(this).data('collapse-groups') : false ;
            var drop_right = ($(this).data('drop-right')) ? $(this).data('drop-right') : false ;
            var drop_up = ($(this).data('drop-up')) ? $(this).data('drop-up') : false ;
            var select_all = ($(this).data('select-all')) ? $(this).data('select-all') : false ;
            var width = ($(this).data('width')) ? $(this).data('width') : '' ;
            var height = ($(this).data('height')) ? $(this).data('height') : '' ;
            var filter = ($(this).data('filter')) ? $(this).data('filter') : false ;

            // advanced functions
            var onchange_function = function(option, checked, select) {
                alert('Changed option ' + $(option).val() + '.');
            }
            var dropdownshow_function = function(event) {
                alert('Dropdown shown.');
            }
            var dropdownhide_function = function(event) {
                alert('Dropdown Hidden.');
            }

            // init advanced functions
            var onchange = ($(this).data('action-onchange') == true) ? onchange_function : '';
            var dropdownshow = ($(this).data('action-dropdownshow') == true) ? dropdownshow_function : '';
            var dropdownhide = ($(this).data('action-dropdownhide') == true) ? dropdownhide_function : '';

            // template functions
            // init variables
            var li_template;
            if ($(this).attr('multiple')){
                li_template = '<li class="mt-checkbox-list"><a href="javascript:void(0);"><label class="mt-checkbox"> <span></span></label></a></li>';
            } else {
                li_template = '<li><a href="javascript:void(0);"><label></label></a></li>';
            }

            // init multiselect
            $(this).multiselect({
                enableClickableOptGroups: clickable_groups,
                enableCollapsibleOptGroups: collapse_groups,
                disableIfEmpty: true,
                enableFiltering: filter,
                includeSelectAllOption: select_all,
                dropRight: drop_right,
                buttonWidth: width,
                maxHeight: height,
                onDropdownShow: dropdownshow,
                onDropdownHide: dropdownhide,
                buttonClass: btn_class,
            });
        });
    }

    e = function () {
        var a = new Datatable;
        a.init({
            src: $("#datatable_ajax"), onSuccess: function (a, e) {
            }, onError: function (a) {
            }, onDataLoad: function (a) {
            }, loadingMessage: "Loading...", dataTable: {
                bStateSave: !0,
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
                lengthMenu: [[25, 50, 100], [25, 50, 100]],
                pageLength: 25,
                "columns": [
                    { "data": "id","bSortable": false },
                    { "data": "name" },
                    { "data": "closed_at" },
                    { "data": "customer_email" },
                    { "data": "fulfillment_status" },
                    { "data": "tags" },
                    { "data": "cn_number" },
                    { "data": "destination_city","bSortable": false },
                    { "data": "consignment_address" },
                    { "data": "financial_status" },
                    { "data": "total_price" },
                    { "data": "actions","bSortable": false }
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    url: route('admin.shopify_orders.datatable'),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: !0,
                "fnDrawCallback" : function(e) {},
                order: [[1, "desc"]]
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
            a(), e(), ms();
        }
    }
}();
jQuery(document).ready(function () {
    TableDatatablesAjax.init()
});