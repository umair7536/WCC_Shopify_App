var Home = function () {

    var TableTransform = function () {
        var $table_transform = $('#table-transform');
        $('#transform').click(function () {
            $table_transform.bootstrapTable();
        });
        $('#destroy').click(function () {
            $table_transform.bootstrapTable('destroy');
        });
    }

    var TableStyle = function () {
        var $table_style = $('#table-style');
        // $table_style.bootstrapTable();

        $('#hover, #striped, #condensed').click(function () {
            var classes = 'table';

            if ($('#hover').prop('checked')) {
                classes += ' table-hover';
            }
            if ($('#condensed').prop('checked')) {
                classes += ' table-condensed';
            }
            $('#table-style').bootstrapTable('destroy')
                .bootstrapTable({
                    classes: classes,
                    striped: $('#striped').prop('checked')
                });
        });

        function rowStyle(row, index) {
            var bs_classes = ['active', 'success', 'info', 'warning', 'danger'];

            if (index % 2 === 0 && index / 2 < bs_classes.length) {
                return {
                    classes: bs_classes[index / 2]
                };
            }
            return {};
        }
    }

    var initRevenueByCentre = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_centre'),
            type: 'GET',
            cache: false,
            success: function (response) {
                generateRevenueChart('location_revenue_today', response.today);
                generateRevenueChart('location_revenue_yesterday', response.yesterday);
                generateRevenueChart('location_revenue_last7days', response.last7days);
                generateRevenueChart('location_revenue_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var initRevenueByService = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_service'),
            type: 'GET',
            cache: false,
            success: function (response) {
                generateRevenueChart('service_revenue_today', response.today);
                generateRevenueChart('service_revenue_yesterday', response.yesterday);
                generateRevenueChart('service_revenue_last7days', response.last7days);
                generateRevenueChart('service_revenue_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var initMyRevenueByCentre = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_centre'),
            type: 'GET',
            data: {
                performance: '1'
            },
            cache: false,
            success: function (response) {
                generateRevenueChart('my_location_revenue_today', response.today);
                generateRevenueChart('my_location_revenue_yesterday', response.yesterday);
                generateRevenueChart('my_location_revenue_last7days', response.last7days);
                generateRevenueChart('my_location_revenue_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var initMyRevenueByService = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_service'),
            type: 'GET',
            data: {
                performance: '1'
            },
            cache: false,
            success: function (response) {
                generateRevenueChart('my_service_revenue_today', response.today);
                generateRevenueChart('my_service_revenue_yesterday', response.yesterday);
                generateRevenueChart('my_service_revenue_last7days', response.last7days);
                generateRevenueChart('my_service_revenue_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var generateRevenueChart = function (id, data) {
        if (typeof(AmCharts) === 'undefined' || $('#' + id).size() === 0) {
            return;
        }

        var chart = AmCharts.makeChart(id, {
            "type": "pie",
            "theme": "light",
            // "path": "../assets/global/plugins/amcharts/ammap/images/",
            "dataProvider": data,
            "valueField": "value",
            "titleField": "centre",
            "outlineAlpha": 0.4,
            "depth3D": 15,
            "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
            "angle": 30,
            "export": {
                "enabled": true
            }
        });
    }

    var initAppointmentsByStatus = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_status'),
            type: 'GET',
            cache: false,
            success: function (response) {
                generateCountChart('appointment_status_today', response.today);
                generateCountChart('appointment_status_yesterday', response.yesterday);
                generateCountChart('appointment_status_last7days', response.last7days);
                generateCountChart('appointment_status_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var initAppointmentsByType = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_type'),
            type: 'GET',
            cache: false,
            success: function (response) {
                generateCountChart('appointment_type_today', response.today);
                generateCountChart('appointment_type_yesterday', response.yesterday);
                generateCountChart('appointment_type_last7days', response.last7days);
                generateCountChart('appointment_type_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var initMyAppointmentsByStatus = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_status'),
            type: 'GET',
            data: {
                performance: '1'
            },
            cache: false,
            success: function (response) {
                generateCountChart('my_appointment_status_today', response.today);
                generateCountChart('my_appointment_status_yesterday', response.yesterday);
                generateCountChart('my_appointment_status_last7days', response.last7days);
                generateCountChart('my_appointment_status_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var initMyAppointmentsByType = function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_type'),
            type: 'GET',
            data: {
                performance: '1'
            },
            cache: false,
            success: function (response) {
                generateCountChart('my_appointment_type_today', response.today);
                generateCountChart('my_appointment_type_yesterday', response.yesterday);
                generateCountChart('my_appointment_type_last7days', response.last7days);
                generateCountChart('my_appointment_type_thismonth', response.thismonth);
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    var generateCountChart = function (id, data) {
        if (typeof(AmCharts) === 'undefined' || $('#' + id).size() === 0) {
            return;
        }

        var chart = AmCharts.makeChart(id, {
            "type": "pie",
            "theme": "light",
            // "path": "../assets/global/plugins/amcharts/ammap/images/",
            "dataProvider": data,
            "valueField": "value",
            "titleField": "appointment",
            "outlineAlpha": 0.4,
            "depth3D": 15,
            "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
            "angle": 30,
            "export": {
                "enabled": true
            }
        });
    }

    return {

        //main function to initiate the module
        init: function () {

            TableTransform();
            TableStyle();
        },
        initRevenueByService: initRevenueByService,
        initRevenueByCentre: initRevenueByCentre,
        initMyRevenueByService: initMyRevenueByService,
        initMyRevenueByCentre: initMyRevenueByCentre,

        initAppointmentsByStatus: initAppointmentsByStatus,
        initAppointmentsByType: initAppointmentsByType,
        initMyAppointmentsByStatus: initMyAppointmentsByStatus,
        initMyAppointmentsByType: initMyAppointmentsByType,
    };

}();

jQuery(document).ready(function () {
    Home.init();
});