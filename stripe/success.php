<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'shared.php';
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/SMTP.php';

// Retrieve the Checkout Session for the successful payment flow that just
// completed. This will be displayed in a `pre` tag as json in this file.
if(isset($_GET['session_id'])){
  $checkout_session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
} else {
  header("Location: canceled.html");
  die();
}


// --- Additional step: retrieve encrypted records by UUID from DB, decrypt, and email in batch ---
// Find UUID keys in metadata
$uuids = [];
if (!empty($checkout_session['metadata'])) {
  $meta = $checkout_session['metadata'];
  $v = $meta['applicant_uuids'];

  // if value contains multiple uuids separated by commas, split
  if (is_string($v) && strpos($v, ',') !== false) {
    foreach (explode(',', $v) as $part) {
      $part = trim($part);
      if ($part !== '') $uuids[] = $part;
    }
  } else {
    $uuids[] = $v;
  }
}

// Initialize output variables
$collectedBodyHTML = [];
$collectedRecords = [];
$emailSuccess = [];
$emailErrors = [];
$debugRecords = [];
$photoAttachments = [];
$attachmentNames = [];

                      
$eHost = $_ENV['HOST'];
$ePort = $_ENV['PORT'];
$eUser = $_ENV['USERNAME'];
$ePass = $_ENV['PASSWORD'];
$eFrom = $_ENV['FROM'];
$dbHost = $_ENV['DB_HOST'] ?? 'localhost:3306';
$dbName = $_ENV['DB_NAME'] ?? null;
$dbUser = $_ENV['DB_USER'] ?? null;
$dbPass = $_ENV['DB_PASS'] ?? null;

$customer = $checkout_session['customer_details'];
$ccEmail = $customer['email'];
$transactionId = $checkout_session['payment_intent'];


if (!empty($uuids)) {  
  if ($dbName && $dbUser) {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
      error_log('DB connect failed in success.php: ' . $mysqli->connect_error);
    } else {
      $updt = $mysqli->prepare("UPDATE encrypted_members SET transactionId=?, email=? WHERE  uuid = ?");  
      $stmt = $mysqli->prepare("SELECT uuid, `type`, data, created_at FROM encrypted_members WHERE uuid = ? LIMIT 1");
      if ($stmt) {
        // decryption helper
        function decrypt_record($b64, $key) {
          $raw = base64_decode($b64);
          if ($raw === false || strlen($raw) < 17) return false;
          $iv = substr($raw, 0, 16);
          $cipher = substr($raw, 16);
          $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
          return $plain === false ? false : $plain;
        }

        $enc_key = $_ENV['ENCRYPTION_KEY'] ?? null;

        // STEP 1: Retrieve and decrypt all records
        foreach ($uuids as $u) {
          $updt->bind_param('sss', $transactionId, $ccEmail, $u);
          $updt->execute();

          $stmt->bind_param('s', $u);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($row = $res->fetch_assoc()) {
            if (!$enc_key) {
              error_log('ENCRYPTION_KEY not set; cannot decrypt for uuid ' . $u);
              continue;
            }
            
            $plain = decrypt_record($row['data'], $enc_key);
            if ($plain === false) {
              error_log('Failed to decrypt record for uuid ' . $u);
              continue;
            }
            // decrypted payload is JSON; decode to associative array
            $decoded = json_decode($plain, true);
            if (!is_array($decoded)) {
              error_log('Decrypted record is not valid JSON for uuid ' . $u . ': ' . substr($plain, 0, 200));
              continue;
            }

            // Store the record for batch processing
            $collectedRecords[] = $decoded;
            $debugRecords[] = [
              'firstname' => $decoded['firstname'] ?? '',
              'lastname' => $decoded['lastname'] ?? '',
              'clubs' => isset($decoded['clubs']) && is_array($decoded['clubs']) ? count($decoded['clubs']) : 0,
              'courses' => isset($decoded['courses']) && is_array($decoded['courses']) ? count($decoded['courses']) : 0
            ];
          } else {
            error_log('No DB record found for uuid ' . $u);
          }
        }
        $stmt->close();

        // STEP 2: Build batch email from all collected records
        if (!empty($collectedRecords)) {
          $batchBodyHTML = '';
          $batchBodyText = '';

          foreach ($collectedRecords as $plain) {
            // Initialize per-record variables
            $bodyHTML = '';
            $bodyText = '';
            $isUpdate = isset($plain['applicationType']) && $plain['applicationType'] === 'update';

            switch( $plain['applicationType'] ) {
              case 'new':
                $appType = 'New';
                break;
              case 'half':
                $appType = 'New Half-Year';
                break;
              case 'renew':
                $appType = 'Renewal';
                break;
              case 'update':
                $appType = 'Update';
                break;
              default:
                $appType = 'New';
            }

            switch( $plain['membershipFee']){
              case 'general':
                $feeType = 'General Membership';
                break;
              case 'senior':
                $feeType = 'Senior Membership';
                break;
              case 'junior':
                $feeType = 'Junior Membership';
                break;
              case 'archery':
                $feeType = 'Archery Membership';
                break;
              case 'update':
                $feeType= 'Family Membership';
                break;
              default:
                $feeType = 'General Membership';
            }            

            $firstName = $plain['firstname'];
            $lastName = $plain['lastname'];

            if(!$isUpdate){
                          $dob = $plain['dob'];
                        $address = $plain['address'].', '.$plain['city'].', '.$plain['province'].', '.$plain['postal']; 
                        $email = $plain['email'];
                        $disciplines = implode(',', $plain['disciplines']);
            }
            $extraCards = $plain['extra'];

            $bodyHTML = "<strong>$appType $feeType</strong><br />";
            $bodyHTML .= (strlen($plain['pfgaNumber']) > 0) ? '<strong>Card #</strong>: '.$plain['pfgaNumber'].'<br />' : '';   
            $bodyHTML .= "<strong>First Name</strong>: $firstName<br />";
            $bodyHTML .= "<strong>Last Name</strong>: $lastName<br />"; 
            if(!$isUpdate){
              $bodyHTML .= (strlen($plain['alias']) > 0) ? '<strong>Preferred Name</strong>: '.$plain['alias'].'<br />' : '';
              $bodyHTML .= "<strong>Date of Birth</strong>: $dob<br />";  
              $bodyHTML .= "<strong>Address</strong>: $address<br />";  
              $bodyHTML .= (strlen($plain['homephone']) > 0) ? '<strong>Home Phone</strong>: '.$plain['homephone'].'<br />':'';  
              $bodyHTML .= (strlen($plain['cellphone']) > 0) ? '<strong>Cell Phone</strong>: '.$plain['cellphone'].'<br />':'';
              $bodyHTML .= "<strong>Email</strong>: $email<br />";
              $bodyHTML .= ($plain['palType']=='noPal') ? 'None<br />' : "<strong>PAL</strong>: ".$plain['palNum'].' expires: '.$plain['palExpiry'].'<br />' ;  
              $bodyHTML .= (strlen($plain['palDate'])>0) ? '<strong>Approx PAL Date</strong>: '.$plain['palDate'].'<br />' :''; 
              $bodyHTML .= "<strong>Disciplines</strong>: $disciplines<br />";
            }
            $bodyHTML .= "<strong>Extra Cards Ordered</strong>: $extraCards<br />";  
            if ($plain['family'] > 0) {
              $bodyHTML .= '<p><strong>Family Members</strong><br/>';
              foreach ($plain['family'] as $f) {
                $bodyHTML .= $f['firstname'].' '.$f['lastname'].', PAL: '.$f['pal'].' '.$f['palExpiry'].', DoB: '.$f['dob'].'<br />';
              };
              $bodyHTML .= "</p>";
            };
            if(!$isUpdate){
              if(isset($plain['clubs']) && is_array($plain['clubs'])){
                $bodyHTML .= '<p><strong>Club Affiliations</strong><br />';
                foreach ( $plain['clubs'] as $c) {
                  $bodyHTML .= $c['name'].', '.$c['city'].', '.$c['from'].' -'. $c['to'].'<br />';                
                };
                $bodyHTML .= "</p>";
              };
              if(isset($plain['courses']) && is_array($plain['courses'])){
                $bodyHTML .= '<strong>Course/Training</strong><br />';
                foreach ($plain['courses'] as $c) {
                  $bodyHTML .= $c['desc'].', '.$c['location'].', '.$c['trainer'].', '. $c['date'].'<br />';
                };
                $bodyHTML .= "</p>";
              };
            }
            


            $bodyText = "$appType $feeType\n";
            $bodyText .= (strlen($plain['pfgaNumber']) > 0) ? 'Card #: '.$plain['pfgaNumber'].'\n' : '';   
            $bodyText .= "First Name: $firstName\n";
            $bodyText .= "Last Name: $lastName\n"; 
            if($appType != 'Update'){
              $bodyText .= (strlen($plain['alias']) > 0) ? 'Preferred Name: '.$plain['alias'].'\n' : '';
              $bodyText .= "Date of Birth: $dob\n";  
              $bodyText .= "Address: $address\n";  
              $bodyText .= (strlen($plain['homephone']) > 0) ? 'Home Phone: '.$plain['homephone'].'\n':'';  
              $bodyText .= (strlen($plain['cellphone']) > 0) ? 'Cell Phone: '.$plain['cellphone'].'\n':'';
              $bodyText .= "Email: $email\n";
              $bodyText .= ($plain['palType']=='noPal') ? 'None' : "PAL: ".$plain['palNum'].' Expires: '.$plain['palExpiry']."\n";  
              $bodyText .= (strlen($plain['palDate'])>0) ? 'Approx PAL Date: '.$plain['palDate']."\n" :''; 
              $bodyText .= "Disciplines: $disciplines\n";
            }
            $bodyText .= "Extra Cards Ordered: $extraCards\n";  
            if ($plain['family'] > 0) {
              $bodyText .= 'Family Members\n';
              foreach ($plain['family'] as $f) {
                $bodyText .= $f['firstname'].' '.$f['lastname'].', PAL: '.$f['pal'].' '.$f['palExpiry'].', DoB: '.$f['dob'].'\n';
              };
            };

            if(!$isUpdate){
              if(isset($plain['clubs']) && is_array($plain['clubs'])){
                $bodyText .= 'Club Affiliations\n';
                foreach ( $plain['clubs'] as $c) {
                  $bodyText .= $c['name'].', '.$c['city'].', '.$c['from'].' -'. $c['to'].'\n';                 
                };
              };
              if(isset($plain['courses']) && is_array($plain['courses'])){
                $bodyText .= 'Course/Training\n';
                foreach ($plain['courses'] as $c) {
                  $bodyText .= $c['desc'].', '.$c['location'].', '.$c['trainer'].', '. $c['date'].'\n';
                };
              };
            }       
            
            if (isset($plain['photo']['data']) && strlen($plain['photo']['data']) > 0) {
                error_log($plain['photo']['name']);
                error_log($plain['photo']['data']);
              $photoBytes = base64_decode($plain['photo']['data']);
              if ($photoBytes !== false) {
                $photoType = trim($plain['photo']['type'] ?? '');
                $photoName = trim($plain['photo']['name'] ?? '');
                $photoName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $photoName);
                $extension = '';
                if ($photoType === 'image/jpeg' || $photoType === 'image/jpg') {
                  $extension = 'jpg';
                } elseif ($photoType === 'image/png') {
                  $extension = 'png';
                } elseif ($photoName !== '') {
                  $detected = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
                  if (in_array($detected, ['jpg','jpeg','png'], true)) {
                    $extension = $detected === 'jpeg' ? 'jpg' : $detected;
                  }
                }
                if ($extension === '') {
                  $extension = 'jpg';
                }
                if ($photoName === '' || pathinfo($photoName, PATHINFO_EXTENSION) === '') {
                  $photoName = sprintf('%s_%s_photo.%s', preg_replace('/[^A-Za-z0-9]+/', '_', $firstName), preg_replace('/[^A-Za-z0-9]+/', '_', $lastName), $extension);
                } else {
                  $photoName = pathinfo($photoName, PATHINFO_FILENAME) . '.' . $extension;
                }
                $uniqueName = $photoName;
                $suffix = 1;
                while (isset($attachmentNames[$uniqueName])) {
                  $uniqueName = pathinfo($photoName, PATHINFO_FILENAME) . '-' . $suffix . '.' . $extension;
                  $suffix++;
                }
                $attachmentNames[$uniqueName] = true;
                $photoAttachments[] = [
                  'data' => $photoBytes,
                  'filename' => $uniqueName,
                  'type' => $photoType ?: 'application/octet-stream',
                  'applicant' => trim($firstName . ' ' . $lastName)
                ];
              } else {
                error_log('Invalid photo base64 for applicant ' . $firstName . ' ' . $lastName);
              }
            }
            // Attach any family member photos for this applicant
            if (isset($plain['family']) && is_array($plain['family'])) {
              foreach ($plain['family'] as $f) {
                if (!is_array($f)) continue;
                if (!isset($f['photo']) || !isset($f['photo']['data']) || strlen($f['photo']['data']) === 0) continue;
                $photoBytes = base64_decode($f['photo']['data']);
                if ($photoBytes === false) {
                  error_log('Invalid family photo base64 for ' . ($f['firstname'] ?? '') . ' ' . ($f['lastname'] ?? ''));
                  continue;
                }
                $photoType = trim($f['photo']['type'] ?? '');
                $photoName = trim($f['photo']['name'] ?? '');
                $photoName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $photoName);
                $extension = '';
                if ($photoType === 'image/jpeg' || $photoType === 'image/jpg') {
                  $extension = 'jpg';
                } elseif ($photoType === 'image/png') {
                  $extension = 'png';
                } elseif ($photoName !== '') {
                  $detected = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
                  if (in_array($detected, ['jpg','jpeg','png'], true)) {
                    $extension = $detected === 'jpeg' ? 'jpg' : $detected;
                  }
                }
                if ($extension === '') $extension = 'jpg';
                if ($photoName === '' || pathinfo($photoName, PATHINFO_EXTENSION) === '') {
                  $photoName = sprintf('%s_%s_photo.%s', preg_replace('/[^A-Za-z0-9]+/', '_', ($f['firstname'] ?? '')), preg_replace('/[^A-Za-z0-9]+/', '_', ($f['lastname'] ?? '')), $extension);
                } else {
                  $photoName = pathinfo($photoName, PATHINFO_FILENAME) . '.' . $extension;
                }
                $uniqueName = $photoName;
                $suffix = 1;
                while (isset($attachmentNames[$uniqueName])) {
                  $uniqueName = pathinfo($photoName, PATHINFO_FILENAME) . '-' . $suffix . '.' . $extension;
                  $suffix++;
                }
                $attachmentNames[$uniqueName] = true;
                $photoAttachments[] = [
                  'data' => $photoBytes,
                  'filename' => $uniqueName,
                  'type' => $photoType ?: 'application/octet-stream',
                  'applicant' => trim(($f['firstname'] ?? '') . ' ' . ($f['lastname'] ?? ''))
                ];
              }
            }
            // Store for display on page
            $collectedBodyHTML[] = $bodyHTML;

            // Append to batch body (separator between applicants)
            $batchBodyHTML .= $bodyHTML . "<br /><hr><br />";
            $batchBodyText .= $bodyText . "\n---\n";
          }

          // STEP 3: Send single batch email with all applicants
          $pm = new PHPMailer();
          $pm->isSMTP();
          $pm->CharSet = 'UTF-8';
          $pm->Host = $eHost;
          $smtpDebug = '';
          $pm->SMTPDebug = 0; // Set to 2 for detailed debug output
          $pm->Debugoutput = function($str, $level) use (&$smtpDebug) { $smtpDebug .= "[$level] $str\n"; };
          $pm->SMTPAuth = True;
          $pm->Port = $ePort;
          $pm->Username = $eUser;
          $pm->Password = $ePass;
          $pm->setFrom($eFrom);
          $pm->addAddress($eFrom);
          if (!empty($ccEmail)) $pm->AddCC($ccEmail);
          $pm->isHTML(true);

          foreach ($photoAttachments as $attachment) {
            $pm->addStringAttachment($attachment['data'], $attachment['filename'], 'base64', $attachment['type']);
          }
          
          $appCountStr = count($collectedRecords) === 1 ? 'Applicant' : 'Applicants (' . count($collectedRecords) . ')';
          $pm->Subject = "PFGA $appCountStr from Stripe";
          $pm->Body = "<html><body><p>Hello Membership Secretary,</p><p>The following $appCountStr submitted via Stripe:</p>" . $batchBodyHTML . "<p>Thanks,<br />The PFGA Stripe Application</p></body></html>";
          $pm->AltBody = "Hello Membership Secretary,\n\nThe following $appCountStr submitted via Stripe:\n\n" . $batchBodyText . "\n\nThanks,\nThe PFGA Stripe Application";

          if ($pm->send()) {
            $emailSuccess[] = 'Batch email sent for ' . count($collectedRecords) . ' applicant(s)';
            
            // Mark all records as emailed in DB
            if (isset($mysqli) && $mysqli instanceof mysqli) {              
              foreach ($uuids as $uuid) {
                $u_stmt = $mysqli->prepare("UPDATE encrypted_members SET emailed = 1, emailed_at = ? WHERE uuid = ?");
                if ($u_stmt) {
                  $now = date('Y-m-d H:i:s');
                  $u_stmt->bind_param('ss', $now, $uuid);
                  $u_stmt->execute();
                  $u_stmt->close();
                } else {
                  error_log('Failed to prepare update statement for uuid ' . $uuid . ': ' . $mysqli->error);
                }
              }
            }



          } else {
            $emailErrors[] = ['message' => 'Batch email failed', 'error' => $pm->ErrorInfo, 'debug' => $smtpDebug];
            error_log('Failed to send batch email: ' . $pm->ErrorInfo . '\n' . $smtpDebug);

            // Persist failure to DB log table if DB connection is available
            if (isset($mysqli) && $mysqli instanceof mysqli) {
              $logStmt = $mysqli->prepare("INSERT INTO log (`level`, `message`, `details`, `created_at`) VALUES (?, ?, ?, ?)");
              if ($logStmt) {
                $level = 'error';
                $message = 'Failed to send batch email';
                $details = json_encode([
                  'error' => $pm->ErrorInfo,
                  'debug' => $smtpDebug,
                  'uuids' => $uuids,
                  'transaction' => $checkout_session['payment_intent'] ?? null
                ]);
                $now = date('Y-m-d H:i:s');
                $logStmt->bind_param('ssss', $level, $message, $details, $now);
                $logStmt->execute();
                if ($logStmt->errno) {
                  error_log('Failed to insert log record: ' . $logStmt->error);
                }
                $logStmt->close();
              } else {
                error_log('Failed to prepare log insert statement: ' . $mysqli->error);
              }
            }
          }
        }
      } else {
        error_log('DB prepare failed in success.php: ' . $mysqli->error);
      }
      $mysqli->close();
    }
  } else {
    error_log('DB credentials not configured; cannot retrieve encrypted records.');
  }
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
      <div class="sr-loading">
        <image src="..\images\ekkant-loader-9342.gif" />
      </div>
      <div class="sr-main hidden">
        <div class="sr-payment-summary completed-view">
          <h1>Your payment succeeded</h1>
        </div>
        <p>
          You have successfully completed your application(s), new members will be considered at the next Board of Directors meeting and you will be contacted<br />
          You will be contacted sometime after the meeting to inform you if you have been accepted or not.<br />
          
          The following has been emailed to the Membership Secretary and to you:
        </p>
        
          <div class="sr-section completed-view">
            <?php

              // Show individual records (for reference/display)
              if (!empty($collectedBodyHTML)) {
                echo '<div class="alert alert-info mt-2"><h3>Application Details</h3>';
                $count = count($collectedBodyHTML);
                foreach ($collectedBodyHTML as $idx => $b) {
                  echo "<div class=\"body-record\">";
                  echo $b;
                  echo "</div>";
                  if ($idx < $count - 1) echo "<hr />";
                }
                echo '</div>';
              }

            ?>
          </div>
          <div class="sr-section">
            If you are a new member, as part of the new member application process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca. The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured, background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. 
          </div>
        </div>
      </div>
    </div>
    
    <script src="../js/bootstrap.js" type="text/javascript"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="../js/vue.js"></script>
  </body>
<script>
  // Hide the loading indicator and reveal the main content after email send completes
  (function(){
    var emailSent = <?php echo (!empty($emailSuccess) && count($emailSuccess) > 0) ? 'true' : 'false'; ?>;
    window.addEventListener('load', function(){
      try {
        if (emailSent) {
          var loader = document.querySelector('.sr-loading') || document.querySelector('.loading') || document.getElementById('loader');
          if (loader) loader.style.display = 'none';
          var main = document.querySelector('.sr-root .sr-main') || document.querySelector('.sr-main') || document.querySelector('.sr-root') || document.getElementById('main');
          if (main) {
            try { main.classList.remove('hidden'); } catch(e) {}
            // Ensure visible regardless of CSS rule
            main.style.display = 'block';
          }
        }
      } catch (e) { console.error(e); }
    });
  })();
</script>
</html>
