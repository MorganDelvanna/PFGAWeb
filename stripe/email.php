<?php
require_once 'shared.php';

if (isset($_SESSION['user']))
{
    $user = $_SESSION['user'];
    $loggedIn = TRUE;
}
else {
    echo "You are not logged in";
    die;
}

$decryption_helper = null;
function decrypt_record($b64, $key) {
    $raw = base64_decode($b64);
    if ($raw === false || strlen($raw) < 17) return false;
    $iv = substr($raw, 0, 16);
    $cipher = substr($raw, 16);
    $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $plain === false ? false : $plain;
}

$collectedRecords = [];
$collectedBodyHTML = [];
$debugRecords = [];

// Determine month filter from GET params (default to current month)
$filterYear = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$filterMonth = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
if ($filterMonth < 1) $filterMonth = 1;
if ($filterMonth > 12) $filterMonth = 12;
$startDate = DateTimeImmutable::createFromFormat('!Y-n-j', "$filterYear-$filterMonth-1");
if (!$startDate) {
    $startDate = new DateTimeImmutable('first day of this month');
}
$endDate = $startDate->modify('+1 month');
$startStr = $startDate->format('Y-m-d 00:00:00');
$endStr = $endDate->format('Y-m-d 00:00:00');
$monthLabel = $startDate->format('F Y');
$prevDate = $startDate->modify('-1 month');
$nextDate = $startDate->modify('+1 month');
$prevParams = 'year=' . $prevDate->format('Y') . '&month=' . $prevDate->format('n');
$nextParams = 'year=' . $nextDate->format('Y') . '&month=' . $nextDate->format('n');

$dbHost = $_ENV['DB_HOST'] ?? 'localhost:3306';
$dbName = $_ENV['DB_NAME'] ?? null;
$dbUser = $_ENV['DB_USER'] ?? null;
$dbPass = $_ENV['DB_PASS'] ?? null;


if ($dbName && $dbUser) {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
        error_log('DB connect failed in email.php: ' . $mysqli->connect_error);
    } else {
        $stmt = $mysqli->prepare("SELECT uuid, `type`, data, created_at FROM encrypted_members WHERE transactionId IS NULL AND created_at >= ? AND created_at < ? ORDER BY created_at DESC");
        if (!$stmt) {
            error_log('DB prepare failed in email.php: ' . $mysqli->error);
        } else {
            $enc_key = $_ENV['ENCRYPTION_KEY'] ?? null;
            // bind the date range for the selected month
            $stmt->bind_param('ss', $startStr, $endStr);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                $found = false;
                while ($row = $res->fetch_assoc()) {
                    $found = true;
                    $uuid = $row['uuid'] ?? '';
                    if (!$enc_key) {
                        error_log('ENCRYPTION_KEY not set; cannot decrypt for uuid ' . $uuid);
                        continue;
                    }

                    $plain = decrypt_record($row['data'], $enc_key);
                    if ($plain === false) {
                        error_log('Failed to decrypt record for uuid ' . $uuid);
                        continue;
                    }
                    $decoded = json_decode($plain, true);
                    if (!is_array($decoded)) {
                        error_log('Decrypted record is not valid JSON for uuid ' . $uuid . ': ' . substr($plain, 0, 200));
                        continue;
                    }
                    
                    $email = $decoded['email'] ?? 'badEmail@thing.com';

                    $targetDate = $row['created_at'] ?? null;
                    $startTimestamp = strtotime($targetDate . ' -1 hours');
                    $endTimestamp   = strtotime($targetDate . ' +1 hours');

                    // find related Payment Intents                
                    $pis = $stripe->paymentIntents->all([
                        'created' => [
                            'gte' => $startTimestamp,
                            'lt' => $endTimestamp,
                        ],
                        'expand' => [ 'data.latest_charge']
                    ]);

                    $status = null;
                    $paymentIntent = null;
                    foreach ($pis-> autoPagingIterator() as $pi) {
                        $charge = $pi->latest_charge;
                        if ($charge && strtolower($charge->billing_details->email ?? '') === strtolower($email)) {
                            $paymentIntent = $pi;
                            $status = ($pi->status ?? '');
                            break;
                        }
                    }
                    // pi_3To6YAB2ie8bsWmg16WMRg2x

                    // Attach payment intent info to the decoded record (keep as array)
                    
                    $decoded['uuid'] = $uuid;
                    // Preserve DB created_at and a convenient payment status
                    $decoded['created_at'] = $row['created_at'] ?? null;
                    $decoded['payment_status'] = $status ?? '';
                    $collectedRecords[] = $decoded;
                    $debugRecords[] = [
                        'firstname' => $decoded['firstname'] ?? '',
                        'lastname' => $decoded['lastname'] ?? '',
                        'email' => $decoded['email'] ?? '',
                    ];
                }
                if (!$found) {
                    error_log('No DB records found matching query for encrypted_members with NULL transactionId');
                }
            } else {
                error_log('Failed to get result set from statement: ' . $mysqli->error);
            }
            $stmt->close();
        }
        $mysqli->close();
    }
} else {
    error_log('DB credentials not configured; cannot retrieve encrypted records.');
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>PFGA Payment Successful</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pfga.css">
    <link rel="stylesheet" href="../css/menu.css" />   
    <link rel="icon" href="../images/pfgalogo.ico" type="image/icon type">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
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
          <h1>Applicants without email records</h1>
        </div>
        <div class="completed-view">
            <a href="blacklist.php">Blacklist</a>
        </dir>

          <div class="sr-section completed-view">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <a href="?<?php echo htmlspecialchars($prevParams); ?>" class="btn btn-sm btn-outline-primary">&laquo; <?php echo $prevDate->format('F Y'); ?></a>
              </div>
              <div><strong><?php echo htmlspecialchars($monthLabel); ?></strong></div>
              <div>
                <a href="?<?php echo htmlspecialchars($nextParams); ?>" class="btn btn-sm btn-outline-primary"><?php echo $nextDate->format('F Y'); ?> &raquo;</a>
              </div>
            </div>

            <table class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Form Submitted</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Email</th>
                  <th>Payment Status</th>
                </tr>
              </thead>
              <tbody>
              <?php
                if (!empty($collectedRecords)) {
                  foreach ($collectedRecords as $idx => $record) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($record['created_at'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($record['firstname'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($record['lastname'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($record['email'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($record['payment_status'] ?? '') . "</td>";
                    echo "<td><button id='btnResend_" . htmlspecialchars($record['uuid']) . "' class='btn btn-sm btn-outline-primary' data-id='" . htmlspecialchars($record['uuid']) . "'>Resend Email</button></td>";
                    echo "</tr>";
                  }
                } else {
                  echo '<tr><td colspan="5" class="text-center">No records for this month.</td></tr>';
                }
              ?>
              </tbody>
            </table>
          </div>
          <div class="sr-section">
           
          </div>
        </div>
      </div>
    </div>
    
    <script src="../js/bootstrap.js" type="text/javascript"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="../js/vue.js"></script>
    <script type="text/javascript">
        $('button[id^="btnResend_"]').click(function() {
            var uuid = $(this).data('id');
            $.ajax({
                url: 'resend_email.php',
                method: 'POST',
                data: { uuid: uuid },
                success: function(response) {
                    alert('Email resend request sent for UUID: ' + uuid);
                },
                error: function(xhr, status, error) {
                    alert('Error resending email for UUID: ' + uuid + '. Error: ' + error);
                }
            });
        });
    </script>
  </body>
</html>
