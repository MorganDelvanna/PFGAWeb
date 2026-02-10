<?php
    session_start();
    
    require '../vendor/autoload.php';

    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);
    

// If the .env file was not configured properly, display a helpful message.
if(!file_exists('.cal_thing')) {
  http_response_code(400);
  ?>
    <p>Environment not set</p>

  <?php
  exit;
}

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '', '.cal_thing');
    $dotenv->load();

    echo "<!DOCTYPE html>\n<html>\n<head>\n";

    require_once "functions.php";

    if (isset($_SESSION['user']))
    {
        $user = $_SESSION['user'];
        $loggedIn = TRUE;
    }
    else $loggedIn = FALSE;

    echo "\t<title>PFGA Calendar</title>\n" . 
        "\t<link rel='stylesheet' href='styles.css' type='text/css' />\n"  .
        "\t<script type='text/javascript' src='scripts\script.js'></script>\n" .
        "</head>\n<body>\n";
?>