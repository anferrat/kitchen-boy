<html lang="en">
  <head>
<?php
include 'fbbot.php';

?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendar</title>
<link rel='stylesheet' href='fullcalendar/fullcalendar.css' />
<link rel='stylesheet' href='css/cal-style.css' /> 
<link href="https://fonts.googleapis.com/css?family=Anton" rel="stylesheet">
<script src='lib/jquery.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='fullcalendar/fullcalendar.js'></script>
<script>
$(function() {

  // page is now ready, initialize the calendar...

  $('#calendar').fullCalendar({
    firstDay: 1,
	displayEventTime: false,
	showNonCurrentDates: false,
	aspectRatio: 1.9,
	eventSources: <?php
  $cal = 'ok';
  echo json_encode(cal_data(sch_gen_screenshot(time()),wash_gen_screenshot(time())));
  ?>
	
	
  })

});
</script>
  </head>
  <body>
<div id='calendar'></div>
  </body>
</html>