<?php
    require_once 'header.php';

    if (!$loggedIn) {

        echo $_SESSION['user'];
        die();
    }
    if (isset($_GET['remove']))
     {
         $remove = sanitizeString($_GET['remove']);
         queryMysql("UPDATE news SET archived = 1 WHERE id='$remove'");
     }
     $result = queryMysql("SELECT id, publish_date, archived, `header`, description FROM news WHERE archived = 0");
     $num = $result->num_rows;
?>

        <div class='main'><h3>News List</h3>
        <br><a href='editNews.php?view=-1' class='button' >Add New News Item</a>
        <div class='container'>
            <table>
                <thead>
                    <th>Date</th>
                    <th>Header</th>
                    <th>Description</th>
                    <th>Actions</th>
                </thead>
                <tbody>
                    <?php
                     for ($j = 0 ; $j < $num ; ++$j)
                        {
                            $row = $result->fetch_array(MYSQLI_ASSOC);
                            $publishDate = $row['publish_date'] ? (new DateTime($row['publish_date']))->format('Y-m-d') : '';                        

                            echo "<tr>" .             
                                "<td>" . $publishDate . " </td> " . 
                                "<td>" . $row['header'] . "</td>" .
                                "<td>". $row["description"] ."</td>".
                                "<td><a class='button' href='editNews.php?view=" . $row['id'] . "'>Edit</a>" .
                                "<a class='button confirmation' href='javascript:deleteNews(" . $row['id'] . ")'>Delete</a></td>" .
                            "</tr>";
                        }
                    ?>
                </tbody>
        </div>
    </div>
</body>
</html>