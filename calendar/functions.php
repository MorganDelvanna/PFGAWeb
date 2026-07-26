<?php
//TODO: Fix the database connection to use environment variables instead of hardcoded values   
    
    $servername = $_ENV['HOST'];
    $username = $_ENV['USERNAME'];
    $password = $_ENV['PASSWORD'];
    $database = $_ENV['DATABASE'];

    $mysqli = mysqli_connect($servername, $username, $password, $database);
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    function queryMySQL($query)
    {
        global $mysqli;
        $result = $mysqli->query($query);
        if (!$result) die ($mysqli->error);
        return $result;
    }

    function destroySession()
    {
        $_SESSION=array();

        if (session_id() != "" || isset($_COOKIE[session_name()]))
            setcookie(session_name(), '', time()-259200, '/');

        session_destroy();
    }

    function sanitizeString($var)
    {
        global $mysqli;
        $var = strip_tags($var);
        $var = htmlentities($var);
        $var = stripslashes($var);
        return $mysqli->real_escape_string($var);
    }

    function getEvent($id)
    {
        $row = new ArrayObject();

        $result = queryMysql("CALL getEventById($id)");
        if ($result->num_rows)
        {
            $row = $result->fetch_array(MYSQLI_ASSOC);
        }

        return $row;
    }

    function getNews($id)
    {
        $row = new ArrayObject();

        $result = queryMysql("SELECT id, publish_date, archived, header, description FROM news WHERE id = $id");
        if ($result->num_rows)
        {
            $row = $result->fetch_array(MYSQLI_ASSOC);
        }

        return $row;
    }

    function saveEvent($id, $start, $end, $title, $description)
    {
        if ($id == -1)
        {
            $result = queryMySQL("INSERT INTO calendar (eventStart, eventEnd, Title, Description, AllDay) VALUES ('$start', '$end', '$title', '$description', 0)");  
        }
        else
        {
            $result = queryMySQL("UPDATE calendar SET eventStart='$start', eventEnd='$end', Title='$title', Description='$description' WHERE ID= $id");
        }
        
        return $result;
    }

    function saveNews($id, $publishDate, $header, $description, $archived)
    {
        if ($id == -1){
            $result = queryMysql("INSERT INTO news (publish_date, header, description, archived) VALUES ('$publishDate', '$header', '$description', 0) ");
        } else {
            $result = queryMysql("UPDATE news  SET publish_date = '$publishDate', header = '$header', description = '$description', archived = $archived");
        }

        return $result;
    }

    function safe_json_encode($value){
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $encoded = json_encode($value, JSON_PRETTY_PRINT);
        } else {
            $encoded = json_encode($value);
        }
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $encoded;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
            case JSON_ERROR_UTF8:
                $clean = utf8ize($value);
                return safe_json_encode($clean);
            default:
                return 'Unknown error'; // or trigger_error() or throw new 
        Exception();
        }
}


function utf8ize($mixed) {
if (is_array($mixed)) {
    foreach ($mixed as $key => $value) {
        $mixed[$key] = utf8ize($value);
    }
} else if (is_string ($mixed)) {
    return utf8_encode($mixed);
}
return $mixed;
}

?>