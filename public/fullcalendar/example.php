<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'/>

    <title>
        1-day vertical resource view - Demos | FullCalendar
    </title>

    <link href='./fullcalendar.min.css' rel='stylesheet'/>
    <link href='./fullcalendar.print.css' rel='stylesheet' media='print'/>
    <link href='./scheduler.min.css' rel='stylesheet'/>
    <script src='./lib/moment.min.js'></script>
    <script src='./lib/jquery.min.js'></script>
    <script src='./fullcalendar.min.js'></script>
    <script src='./scheduler.min.js'></script>


    <style>

        html, body {
            margin: 0;
            padding: 0;
            font-family: "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
            font-size: 14px;
        }

        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }

    </style>


    <script>

        $(function () {

            $('#calendar').fullCalendar({
                businessHours: true,
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                slotDuration: '00:15:00',
                defaultView: 'agendaDay',
                groupByResource: true,
                resources: [
                    {
                        id: 'a',
                        title: 'Dr. Asim',
                        businessHours: {
                            start: '10:00',
                            end: '18:00'
                        }
                    },
                    {
                        id: 'b',
                        title: 'Dr, Ali Ahmed',
                        businessHours: {
                            start: '11:00',
                            end: '17:00'
                        }
                    }
                ],
                events: [
                    // {"resourceId":"a","title":"Conference","start":"2018-05-21","end":"2018-05-23"},
                    // {"resourceId":"b","title":"Birthday Party","start":"2018-05-23T07:00:00+00:00"}
                ]
            });

        });

    </script>

</head>
<body>
    <div id='calendar'></div>
</body>

</html>
