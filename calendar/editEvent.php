<?php

require_once 'header.php';

if (!$loggedIn) die();

echo "<div class='main'>\n\t<h3>Edit Event</h3>\n" .
"\t<br>\n\t<a href='editEvent.php?view=-1' class='button' >Add New Event</a>\n";
 
if (isset($_GET['view']))
{
    $view = sanitizeString($_GET['view']);
    // support copying an existing event into a new event: ?view=-1&copyFrom=ID
    $copyFrom = isset($_GET['copyFrom']) ? intval($_GET['copyFrom']) : null;
    if ($view > -1)
    {
        $data = getEvent($view);
        $start = new DateTime($data['EventStart']);
        $end = new DateTime($data['EventEnd']);
        $title = $data['Title'];
        $desc = $data['Description'];
    } else {
        if ($copyFrom) {
            // load event to copy but keep view as -1 so saving creates a new event
            $data = getEvent($copyFrom);
            $start = new DateTime($data['EventStart']);
            $end = new DateTime($data['EventEnd']);
            $title = $data['Title'];
            $desc = $data['Description'];
            // ensure eventID hidden field is -1
            $view = -1;
        } else {
            $start = null;
            $end = null;
            $title = '';
            $desc = '';
        }
    }
}
elseif (isset($_POST['eventID']))
{
    $id = sanitizeString($_POST['eventID']);
    if ($id == "") $id = -1;

    $start = sanitizeString($_POST['eventStart']);
    $end = sanitizeString($_POST['eventEnd']);
    $title = sanitizeString($_POST['title']);
    $description = sanitizeString($_POST['description']);

    saveEvent($id, $start, $end, $title, $description);
    echo "<script type='text/javascript'>window.location = 'eventList.php'</script>";

    die();
}
else
{
    die("</div></body></html>");
}
 
?>

<link rel="stylesheet" type="text/css" href="scripts/dateTimePicker/jquery.datetimepicker.min.css" />
<script src="scripts/jquery-1.12.4.js"></script>
<script src="scripts/dateTimePicker/jquery.datetimepicker.full.min.js"></script>
<script src="scripts/moment.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#eventStart').datetimepicker({
            format: 'Y-m-d H:i',
            defaultTime: '9:00',
            closeOnDateSelect: true,
            onSelectDate: function (ct, $i) {
                var start = moment(ct).format('YYYY-MM-DD 21:00')
                $('#eventEnd').val(start);
            }
        });
        $('#eventEnd').datetimepicker({
            format: 'Y-m-d H:i',
            defaultTime: '21:00',
            closeOnDateSelect:true
        });
    });
</script>

    <form  method='post' action='editEvent.php' onsubmit="return validate(this);">
    <div style="margin-top: 5px;">
       <span class='fieldname'>Start Time: </span><input type="text" id="eventStart" name="eventStart" value="<?= (is_null($start) ? '' : $start->format('Y-m-d H:i')) ?>" /><br />
        <span class='fieldname'>End Time: </span><input type="text" id="eventEnd" name="eventEnd" value="<?= (is_null($end) ? '' : $end->format('Y-m-d H:i')) ?>" /><br />
        <span class='fieldname'>Title: </span><input type="text" id="title" name="title" value="<?= $title ?>" /><br />
        <span class='fieldname'>Description: </span><input style="min-width:500px;" type="text" id="description" name="description" value="<?= $desc ?>" /><br />
    </div>
    <div><input type='submit' class='button' value='Save'><a class='button' href='eventList.php'>Back</a></div>
        <input type="hidden" id="eventID" name="eventID" value="<?= $view ?>"/>
    </form>
</body>
</html>


