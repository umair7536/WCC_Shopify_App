let Utils = function () {

    /**
     * update the query string of the url create, update and delete
     * @author github user
     * @url: https://gist.github.com/excalq/2961415?doc=13
     * @param key
     * @param value
     */
    let updateQueryStringParam = function (key, value) {
        if(value!=="" && value!=null){
            let baseUrl = [location.protocol, '//', location.host, location.pathname].join(''),
                urlQueryString = document.location.search,
                newParam = key + '=' + value,
                params = '?' + newParam;

            // If the "search" string exists, then build params from it
            if (urlQueryString) {
                keyRegex = new RegExp('([\?&])' + key + '[^&]*');

                // If param exists already, update it
                if (urlQueryString.match(keyRegex) !== null) {
                    params = urlQueryString.replace(keyRegex, "$1" + newParam);
                } else { // Otherwise, add it to end of query string
                    params = urlQueryString + '&' + newParam;
                }
            }
            window.history.replaceState({}, "", baseUrl + params);
        }else{
            [base_url, queryString] = window.location.href.split("?");
            queryParams = new URLSearchParams(queryString);
            if(queryParams.has(key))
                queryParams.delete(key);
            window.history.replaceState({}, "", base_url + "?" + queryParams.toLocaleString());

        }
    };

    let isUrlHasQueryParam = function (key, url){
        queryString = url.split("?")[1];
        if(queryString){
            queryParams = new URLSearchParams(queryString);
            return queryParams.has(key);
        }else{
            return false;
        }
    };

    /**
     * Toaster notification handler
     * @param type
     * @param message
     */
    let notification = function (type, message) {
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": false,
            "positionClass": "toast-bottom-center",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        if(type=="info")
            toastr.info(message);
        else if(type=="error")
            toastr.error(message);
        else if(type=="warning")
            toastr.warning(message);
    };

    /**
     * Appointments dashboard unscheduled appointment card toggle functionality
     */
    let toggleUnsechuledAppoints = function (){
        $(".card-hide").click(function(){
            if($(".card-hide").hasClass("show-card")){
                $(this).text("Hide Card");
                $(this).removeClass("show-card");
                $(".cards").css("display","block");
                $(".calendar-container").css("padding-left","260px");
            }else{
                $(this).text("Show Card");
                $(this).addClass("show-card");
                $(".cards").css("display","none");
                $(".calendar-container").css("padding-left","0");
            }
        });
        $(".card-hide").trigger("click");
    };


    /**
     * Hide left navigation on running of this funtion
     */
    let hideLeftSideNavigation = function () {
        $("body").addClass("page-sidebar-closed");
        $(".page-sidebar-menu").addClass("page-sidebar-menu-closed");
    };


    /**
     * <h1>Appointments Sections</h1>
     */

    /**
     * update city_id, location_id and doctor_id
     */
    let updateAppointmentsQueryString = function (consulting=true){
        updateQueryStringParam('city_id',$("#city_id").find(":selected").val());
        updateQueryStringParam('location_id',$("#location_id").find(":selected").val());
        updateQueryStringParam('doctor_id',$("#doctor_id").find(":selected").val());
        if(!consulting){
            updateQueryStringParam('machine_id',$("#machine_id").find(":selected").val());
        }
        $("#backurl").val(window.location.href);
    };


    /**
     * Ajax Request Functions
     */

    let ajaxPostRequest = function (url, data, successCallback, errorCallback) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url ,
            type: 'POST',
            data: {
                location_id: locationId
            },
            cache: false,
            success:(response) =>{
                console.log("successful response");
                successCallback(response);
            } ,
            error: (xhr, ajaxOptions, thrownError) => {
                console.log("error callback");
                errorCallback(xhr, ajaxOptions, thrownError);
            }
        });
    };

    let ajaxGetRequest = function (url, successCallback, errorCallback) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url ,
            type: 'GET',
            cache: false,
            success:(response) =>{
                console.log("successful response");
                successCallback(response);
            } ,
            error: (xhr, ajaxOptions, thrownError) => {
                console.log("error callback");
                errorCallback(xhr, ajaxOptions, thrownError);
            }
        });
    };

    return {
        updateQueryStringParam : updateQueryStringParam,
        notification : notification,
        toggleUnsechuledAppoints : toggleUnsechuledAppoints,
        hideLeftSideNavigation : hideLeftSideNavigation,
        updateAppointmentsQueryString : updateAppointmentsQueryString,
        ajaxPostRequest : ajaxPostRequest,
        ajaxGetRequest : ajaxGetRequest
    }
}();