$(document).ready(function () {

    $(document).on('change', '#lead_status_parent_id', function () {
        $('#lead_status_chalid_id').empty();
        var leadstatusp_id = $(this).val();
        $.ajax({
            type: 'get',
            url: route('admin.leads.leadstatus_popup_checks'),
            data: {'id': leadstatusp_id},
            success: function (myarray) {

                if (myarray.lead_status.is_comment == '1') {

                    $('#lead_status_chalid_id').show();

                    var dropdowndata = '<option value="0" selected disabled>Select Lead Status</option>';

                    for (var i = 0; i < myarray.d.length; i++) {
                        dropdowndata += '<option value="' + myarray.d[i].id + '">' + myarray.d[i].name + '</option>';
                    }

                    $('#lead_status_chalid_id')
                        .find('option')
                        .remove()
                        .end()
                        .append(dropdowndata)
                        .val('Select Lead Status')
                    ;
                    $('#lead_status_comment_id').show();
                }
                if (myarray.lead_status.is_comment == '0') {

                    $('#lead_status_chalid_id').show();

                    var dropdowndata = '<option value="0" selected disabled>Select Lead Status</option>';

                    for (var i = 0; i < myarray.d.length; i++) {
                        dropdowndata += '<option value="' + myarray.d[i].id + '">' + myarray.d[i].name + '</option>';
                    }
                    $('#lead_status_chalid_id')
                        .find('option')
                        .remove()
                        .end()
                        .append(dropdowndata)
                        .val('Select Lead Status')
                    ;
                    $('#lead_status_comment_id').hide();
                }
                if (myarray.d == '' && myarray.lead_status.is_comment == '1') {
                    $('#lead_status_chalid_id').hide();
                    $('#lead_status_comment_id').show();
                }

                if (myarray.d == '' && myarray.lead_status.is_comment == '0') {
                    $('#lead_status_chalid_id').hide();
                    $('#lead_status_comment_id').hide();
                }
            },
        });
    });
    $(document).on('change', '#lead_status_chalid_id', function () {
        var leadstatusc_id = $(this).val();
        $.ajax({
            type: 'get',
            url: route('admin.leads.leadstatuschild_popup_checks'),
            data: {'id': leadstatusc_id},
            success: function (myarray) {

                console.log(myarray);

                if (myarray.d.is_comment == '1') {
                    $('#lead_status_comment_id').show();
                }

                if (myarray.d.is_comment == '0') {
                    $('#lead_status_comment_id').hide();
                }
                if (myarray.lead_status2.is_comment == '1') {
                    $('#lead_status_comment_id').show();
                }
            },
        });
    });
});