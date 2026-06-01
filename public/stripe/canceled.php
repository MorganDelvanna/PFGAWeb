<?php
require_once 'shared.php';

// Retrieve checkout session if provided
$uuids = [];
$errors = [];
$updated = [];

if (isset($_GET['session_id']) && !empty($_GET['session_id'])) {
  try {
    $checkout_session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
    if (!empty($checkout_session['metadata'])) {
      $meta = $checkout_session['metadata'];
      if (!empty($meta['applicant_uuids'])) {
        $v = $meta['applicant_uuids'];
        if (is_string($v) && strpos($v, ',') !== false) {
          foreach (explode(',', $v) as $part) {
            $part = trim($part);
            if ($part !== '') $uuids[] = $part;
          }
        } else if (!empty($v)) {
          $uuids[] = $v;
        }
      }
    }
  } catch (Exception $e) {
    $errors[] = 'Failed to retrieve checkout session: ' . $e->getMessage();
  }
}

// If there are UUIDs, update DB
if (!empty($uuids)) {
  $dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
  $dbName = $_ENV['DB_NAME'] ?? null;
  $dbUser = $_ENV['DB_USER'] ?? null;
  $dbPass = $_ENV['DB_PASS'] ?? null;

  if ($dbName && $dbUser) {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
      $errors[] = 'DB connect failed: ' . $mysqli->connect_error;
    } else {
      $stmt = $mysqli->prepare("UPDATE encrypted_members SET canceled_at = ? WHERE uuid = ?");
      if (!$stmt) {
        $errors[] = 'Failed to prepare DB statement: ' . $mysqli->error;
      } else {
        $now = date('Y-m-d H:i:s');
        foreach ($uuids as $u) {
          $stmt->bind_param('ss', $now, $u);
          if ($stmt->execute()) {
            $updated[] = $u;
          } else {
            $errors[] = 'Failed to update uuid ' . $u . ': ' . $stmt->error;
          }
        }
        $stmt->close();
      }
      $mysqli->close();
    }
  } else {
    $errors[] = 'DB credentials not configured.';
  }
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>PFGA Payment Canceled</title>
    <link rel="stylesheet" href="../css/pfga.css">
    <link rel="stylesheet" href="../css/menu.css" />
  </head>
  <body>
    <div class="row">
      <div class="col-12 center">
          <img alt="Peterborough Fish & Game Association" src="../images/header.gif" />
      </div>
    </div>
    <div class="row">
        <div class="col-12 app">
            <menu-control></menu-control>
        </div>
    </div>
    <div class="sr-root">
      <div class="sr-main">
        <div class="sr-payment-summary completed-view">
          <h1>Your payment was canceled</h1>
        </div>
        <div class="sr-section">
          <p>If you cancelled by mistake you may re-submit your application.</p>
        </div>
      </div>
    </div>

    <script src="../js/bootstrap.js" type="text/javascript"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="../js/vue.js"></script>
  </body>
</html>
