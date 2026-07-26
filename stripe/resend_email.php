<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once 'shared.php';
    require '../vendor/PHPMailer/src/Exception.php';
    require '../vendor/PHPMailer/src/PHPMailer.php';
    require '../vendor/PHPMailer/src/SMTP.php';

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

    
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost:3306';
    $dbName = $_ENV['DB_NAME'] ?? null;
    $dbUser = $_ENV['DB_USER'] ?? null;
    $dbPass = $_ENV['DB_PASS'] ?? null;
    
/*
    $dbHost = 'localhost:3306';
    $dbName = 'pfga_forum';
    $dbUser = 'root';
    $dbPass = '1q2w3e4r';*/

    // Initialize output variables
    $collectedBodyHTML = [];
    $collectedRecords = [];
    $attachmentNames = [];
    $photoAttachments = [];

    if ($dbName && $dbUser) {
        $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($mysqli->connect_errno) {
            error_log('DB connect failed in email.php: ' . $mysqli->connect_error);
        } else {
            $uuid = $_POST['uuid'] ?? null;
            $enc_key = $_ENV['ENCRYPTION_KEY'] ?? null;

            $stmt = $mysqli->prepare("SELECT uuid, `type`, data, created_at FROM encrypted_members WHERE uuid = ? LIMIT 1");
            $stmt->bind_param('s', $uuid);
            $stmt->execute();
            $res = $stmt->get_result();
            $batchBodyHTML = '';
            $batchBodyText = '';

            if ($row = $res->fetch_assoc()) {
                if (!$enc_key) {
                    error_log('ENCRYPTION_KEY not set; cannot decrypt for uuid ' . $uuid);
                }
                
                $plain = decrypt_record($row['data'], $enc_key);
                if ($plain === false) {
                    error_log('Failed to decrypt record for uuid ' . $uuid);
                }
                // decrypted payload is JSON; decode to associative array
                $decoded = json_decode($plain, true);
                if (!is_array($decoded)) {
                    error_log('Decrypted record is not valid JSON for uuid ' . $uuid . ': ' . substr($plain, 0, 200));
                }

                $debugRecords[] = [
                'firstname' => $decoded['firstname'] ?? '',
                'lastname' => $decoded['lastname'] ?? '',
                'clubs' => isset($decoded['clubs']) && is_array($decoded['clubs']) ? count($decoded['clubs']) : 0,
                'courses' => isset($decoded['courses']) && is_array($decoded['courses']) ? count($decoded['courses']) : 0
                ];
          } else {
            error_log('No DB record found for uuid ' . $uuid);
          }
          $stmt->close();

          if (!empty($decoded)) {
            $collectedRecords[] = $decoded;
            $bodyHTML = '';
            $bodyText = '';
            $isUpdate = isset($decoded['applicationType']) && $decoded['applicationType'] === 'update';

            switch( $decoded['applicationType'] ) {
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

            switch( $decoded['membershipFee']){
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

            $firstName = $decoded['firstname'];
            $lastName = $decoded['lastname'];

            if(!$isUpdate){
                $dob = $decoded['dob'];
                $address = $decoded['address'].', '.$decoded['city'].', '.$decoded['province'].', '.$decoded['postal']; 
                $email = $decoded['email'];
                $disciplines = implode(',', $decoded['disciplines']);
            }
            $extraCards = $decoded['extra'] ?? 0;

            $bodyHTML = "<strong>$appType $feeType</strong><br />";
            $bodyHTML .= (strlen($decoded['pfgaNumber']) > 0) ? '<strong>Card #</strong>: '.$decoded['pfgaNumber'].'<br />' : '';   
            $bodyHTML .= "<strong>First Name</strong>: $firstName<br />";
            $bodyHTML .= "<strong>Last Name</strong>: $lastName<br />"; 
            if(!$isUpdate){
              $bodyHTML .= (strlen($decoded['alias']) > 0) ? '<strong>Preferred Name</strong>: '.$decoded['alias'].'<br />' : '';
              $bodyHTML .= "<strong>Date of Birth</strong>: $dob<br />";  
              $bodyHTML .= "<strong>Address</strong>: $address<br />";  
              $bodyHTML .= (strlen($decoded['homephone']) > 0) ? '<strong>Home Phone</strong>: '.$decoded['homephone'].'<br />':'';  
              $bodyHTML .= (strlen($decoded['cellphone']) > 0) ? '<strong>Cell Phone</strong>: '.$decoded['cellphone'].'<br />':'';
              $bodyHTML .= "<strong>Email</strong>: $email<br />";
              $bodyHTML .= ($decoded['palType']=='noPal') ? 'None<br />' : "<strong>PAL</strong>: ".$decoded['palNum'].' expires: '.$decoded['palExpiry'].'<br />' ;  
              $bodyHTML .= (strlen($decoded['palDate'])>0) ? '<strong>Approx PAL Date</strong>: '.$decoded['palDate'].'<br />' :''; 
              $bodyHTML .= "<strong>Disciplines</strong>: $disciplines<br />";
            }
            $bodyHTML .= "<strong>Extra Cards Ordered</strong>: $extraCards<br />";  
            if (is_array($decoded['family']) && count($decoded['family']) > 0) {
              $bodyHTML .= '<p><strong>Family Members</strong><br/>';
              foreach ($decoded['family'] as $f) {
                $bodyHTML .= $f['firstname'].' '.$f['lastname'].', PAL: '.$f['pal'].' '.$f['palExpiry'].', DoB: '.$f['dob'].'<br />';
              };
              $bodyHTML .= "</p>";
            };
            if(!$isUpdate){
              if(isset($decoded['clubs']) && is_array($decoded['clubs'])){
                $bodyHTML .= '<p><strong>Club Affiliations</strong><br />';
                foreach ( $decoded['clubs'] as $c) {
                  $bodyHTML .= $c['name'].', '.$c['city'].', '.$c['from'].' -'. $c['to'].'<br />';                
                };
                $bodyHTML .= "</p>";
              };
              if(isset($decoded['courses']) && is_array($decoded['courses'])){
                $bodyHTML .= '<strong>Course/Training</strong><br />';
                foreach ($decoded['courses'] as $c) {
                  $bodyHTML .= $c['desc'].', '.$c['location'].', '.$c['trainer'].', '. $c['date'].'<br />';
                };
                $bodyHTML .= "</p>";
              };
            }

            $bodyText = "$appType $feeType\n";
            $bodyText .= (strlen($decoded['pfgaNumber']) > 0) ? 'Card #: '.$decoded['pfgaNumber'].'\n' : '';   
            $bodyText .= "First Name: $firstName\n";
            $bodyText .= "Last Name: $lastName\n"; 
            if($appType != 'Update'){
              $bodyText .= (strlen($decoded['alias']) > 0) ? 'Preferred Name: '.$decoded['alias'].'\n' : '';
              $bodyText .= "Date of Birth: $dob\n";  
              $bodyText .= "Address: $address\n";  
              $bodyText .= (strlen($decoded['homephone']) > 0) ? 'Home Phone: '.$decoded['homephone'].'\n':'';  
              $bodyText .= (strlen($decoded['cellphone']) > 0) ? 'Cell Phone: '.$decoded['cellphone'].'\n':'';
              $bodyText .= "Email: $email\n";
              $bodyText .= ($decoded['palType']=='noPal') ? 'None' : "PAL: ".$decoded['palNum'].' Expires: '.$decoded['palExpiry']."\n";  
              $bodyText .= (strlen($decoded['palDate'])>0) ? 'Approx PAL Date: '.$decoded['palDate']."\n" :''; 
              $bodyText .= "Disciplines: $disciplines\n";
            }
            $bodyText .= "Extra Cards Ordered: $extraCards\n";  
            if ($decoded['family'] > 0) {
              $bodyText .= 'Family Members\n';
              foreach ($decoded['family'] as $f) {
                $bodyText .= $f['firstname'].' '.$f['lastname'].', PAL: '.$f['pal'].' '.$f['palExpiry'].', DoB: '.$f['dob'].'\n';
              };
            };

            if(!$isUpdate){
              if(isset($decoded['clubs']) && is_array($decoded['clubs'])){
                $bodyText .= 'Club Affiliations\n';
                foreach ( $decoded['clubs'] as $c) {
                  $bodyText .= $c['name'].', '.$c['city'].', '.$c['from'].' -'. $c['to'].'\n';                 
                };
              };
              if(isset($decoded['courses']) && is_array($decoded['courses'])){
                $bodyText .= 'Course/Training\n';
                foreach ($decoded['courses'] as $c) {
                  $bodyText .= $c['desc'].', '.$c['location'].', '.$c['trainer'].', '. $c['date'].'\n';
                };
              };
            }       
            
            if (isset($decoded['photo']['data']) && strlen($decoded['photo']['data']) > 0) {
                error_log($decoded['photo']['name']);
                error_log($decoded['photo']['data']);
              $photoBytes = base64_decode($decoded['photo']['data']);
              if ($photoBytes !== false) {
                $photoType = trim($decoded['photo']['type'] ?? '');
                $photoName = trim($decoded['photo']['name'] ?? '');
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
            // Store for display on page
            $collectedBodyHTML[] = $bodyHTML;

            // Append to batch body (separator between applicants)
            $batchBodyHTML .= $bodyHTML . "<br /><hr><br />";
            $batchBodyText .= $bodyText . "\n---\n";
            $ccEmail = $decoded['email'];

            
            $eHost = $_ENV['HOST'];
            $ePort = $_ENV['PORT'];
            $eUser = $_ENV['USERNAME'];
            $ePass = $_ENV['PASSWORD'];
            $eFrom = $_ENV['FROM'];
            /* 
            $eHost = 'localhost';
            $ePort = 25;
            $eUser = 'dawebguy2@pfga.ca';
            $ePass = '241pizza';
            $eFrom = 'dawebguy2@pfga.ca';
*/
            // TODO: Fix the environment variables for SMTP configuration
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
                    $u_stmt = $mysqli->prepare("UPDATE encrypted_members SET emailed = 1, emailed_at = ?, transactionId=? WHERE uuid = ?");
                    if ($u_stmt) {
                    $now = date('Y-m-d H:i:s');
                    $u_stmt->bind_param('sss', $now, 'resend', $uuid);
                    $u_stmt->execute();
                    $u_stmt->close();
                    } else {
                    error_log('Failed to prepare update statement for uuid ' . $uuid . ': ' . $mysqli->error);
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
          
        }
    } else {
        error_log('DB credentials not configured; cannot retrieve encrypted records.');
    }
?>