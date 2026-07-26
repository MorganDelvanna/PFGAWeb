<?php

require_once 'header.php';

if (!$loggedIn) die();
if (isset($_GET['view']))
{
    $view = sanitizeString($_GET['view']);
    if ($view > -1)
    {
        $data = getNews($view);
        $publishDate = new DateTime($data['publish_date']);
        $title = $data['header'];
        $desc = $data['description'];
    } else {
        $publishDate = null;
        $title = '';
        $desc = '';
    }
}
elseif (isset($_POST['newsID']))
{
    $id = sanitizeString($_POST['newsID']);
    if ($id == "") $id = -1;

    $publishDate = sanitizeString($_POST['publishDate']);
    $title = sanitizeString($_POST['header']);
    $description = addslashes($_POST['description']);
    $description = str_replace("\\n","<br />",$description);
    $archived = intval($_POST['archived']);

    saveNews($id, $publishDate, $title, $description, $archived);
    echo "<script type='text/javascript'>window.location = 'newsList.php'</script>";

    die();
}

 
?>

<link rel="stylesheet" type="text/css" href="scripts/dateTimePicker/jquery.datetimepicker.min.css" />
<script src="scripts/jquery-1.12.4.js"></script>
<script src="scripts/dateTimePicker/jquery.datetimepicker.full.min.js"></script>
<script src="scripts/moment.min.js"></script>
<script src="scripts/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#publishDate').datetimepicker({
            format: 'Y-m-d',
            'showTimepicker': false,
            closeOnDateSelect: true,
            onSelectDate: function (ct, $i) {
                var start = moment(ct).format('YYYY-MM-DD')
            }
        });
        tinymce.init({
            selector: '#description',  // change this value according to your HTML
            plugins: 'a_tinymce_plugin',
            a_plugin_option: true,
            a_configuration_option: 400,
            license_key: "gpl"
        });
    });
</script>
<div class='main'>
    <h3>Edit Event</h3>
    <br><a href='editNews.php?view=-1' class='button' >Add New Item</a>

    <form  method='post' action='editNews.php' onsubmit="return validate(this);">
    <div style="margin-top: 5px;">
       <span class='fieldname'>Start Time: </span><input type="text" id="publishDate" name="publishDate" value="<?= (is_null($publishDate) ? '' : $publishDate->format('Y-m-d H:i')) ?>" /><br />
        <span class='fieldname'>Title: </span><input type="text" id="title" name="header" value="<?= $title ?>" /><br />
        <span class='fieldname'>Description: </span><textarea class="form-control" style="min-width:500px;" type="text" id="description" name="description" ><?= $desc ?></textarea><br />
        <span class="fieldname">Archived: </span><input type="checkbox" value="1" /><br />
    </div>
    <div><input type='submit' class='button' value='Save'><a class='button' href='newsList.php'>Back</a></div>
        <input type="hidden" id="newsID" name="newsID" value="<?= $view ?>"/>
    </form>
</body>
</html>


