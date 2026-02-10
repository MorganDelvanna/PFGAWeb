<?php
    require_once 'header.php';

    if (!$loggedIn) die();


    echo "<div class='main'><h3>Event List</h3>" .
        "<br><a href='editEvent.php?view=-1' class='button' >Add New Event</a>";

    if (isset($_GET['remove']))
     {
         $remove = sanitizeString($_GET['remove']);
         queryMysql("DELETE FROM calendar WHERE ID='$remove'");
     }

    $result = queryMysql("CALL getEvents");
     $num = $result->num_rows;

     echo "<div class='container'>" . 
     "<table><thead><th>Start</th><th>End</th><th>Title</th></thead>";

     for ($j = 0 ; $j < $num ; ++$j)
     {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         $startdate = new DateTime($row['EventStart']);
         $endDate = new DateTime($row['EventEnd']);

         echo "<tr>" .             
         "<td>" . $startdate->format('Y-m-d H:i') . " </td> " . 
         "<td>" . $endDate->format('Y-m-d H:i') . "</td>" .
             "<td>" . $row['Title'] . "</td>" .
             "<td><a class='button' href='editEvent.php?view=" . $row['ID'] . "'>Edit</a></td>" .
             "<td><a class='button confirmation' href='javascript:confirmDelete(" . $row['ID'] . ")'>Delete</a></td>" .
             "</tr>";
     }
     ?>

        </table></div></div>
        </body>
        </html>


