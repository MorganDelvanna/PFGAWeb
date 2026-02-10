<?php
    session_start();
    require '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '', '.cal_thing');
    $dotenv->load();

    require_once 'functions.php';

    // For Local Testing

    // Set the Access-Control-Allow-Origin header to allow requests from the Visual Studio development server
    header("Access-Control-Allow-Origin: http://localhost:54069");

    // Optional: Allow specific HTTP methods (GET, POST, etc.)
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    // Optional: Allow specific headers
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    // Ensure that the browser can access the response (e.g., when using fetch or XMLHttpRequest with credentials)
    header("Access-Control-Allow-Credentials: true");


    if (!$mysqli->multi_query("SELECT id, publish_date, header, description FROM news WHERE archived = 0 and publish_date <= CURDATE()")) {
        echo "CALL failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    do {
        if ($res = $mysqli->store_result()) {
            $rows = array();
            while($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
            $res->free();
        } else {
            if ($mysqli->errno) {
                echo "Store failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo safe_json_encode($rows, JSON_PRETTY_PRINT);
?>

