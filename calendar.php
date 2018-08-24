<!DOCTYPE html>
<html lang="en">
  <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendar</title>
<link rel='stylesheet' href='fullcalendar/fullcalendar.css' />
<script src='lib/jquery.min.js'></script>
<script src='lib/moment.min.js'></script>
<script src='fullcalendar/fullcalendar.js'></script>
<script>
$(function() {

  // page is now ready, initialize the calendar...

  $('#calendar').fullCalendar({
    
	eventSources: <?php
  $cal = 'ok';
  include 'fbbot.php';
  echo json_encode(cal_data(sch_gen(30)));
  ?>
	
	
  })

});
</script>
  </head>
  <body>
  
<div id='calendar'></div>
  </body>
</html>