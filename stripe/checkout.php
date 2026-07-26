<?php
require_once 'shared.php';

$domain_url = $_ENV['DOMAIN'];

if (!empty($_POST['token'])) {
  if (hash_equals($_SESSION['token'], $_POST['token'])) {
    
    // Helper function to generate UUID v4
    function uuidv4() {
      $data = random_bytes(16);
      $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // set version to 0100
      $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // set bits 6-7 to 10
      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // Helper function to encrypt records: AES-256-CBC, store iv+ciphertext as base64
    function encrypt_record($plaintext, $key) {
      $iv = random_bytes(16);
      $cipher = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
      if ($cipher === false) return false;
      return base64_encode($iv . $cipher);
    }

    // Helper function to build items for an applicant
    function buildItemsForApplicant($applicationType, $membershipFee, $env) {
      $items = [];
      
      switch ($applicationType){
        case ('new'):
            $items = [[
                'price' => $env['PRICE_INITIATION'],
                'quantity' => 1
                ]];
            switch($membershipFee){
                case 'general':
                    $items[] = [
                        // General New
                        'price' => $env['PRICE_GENERAL'],
                        'quantity' => 1
                        ];
                    break;
                case 'senior':
                    $items[] = [
                        // Senior New
                        'price' => $env['PRICE_SENIOR'],
                        'quantity' => 1,
                      ];
                    break;
                case 'junior':
                    $items[] = [
                        // Junior New
                        'price' => $env['PRICE_JUNIOR'],
                        'quantity' => 1
                      ];
                    break;
                default:
                  $items[] = [
                    // General New
                    'price' => $env['PRICE_GENERAL'],
                    'quantity' => 1
                    ];
            }
            break;
        case 'half':
            $items = [[
                'price' => $env['PRICE_INITIATION'],
                'quantity' => 1
                ]];
            switch($membershipFee){
                case 'general':
                    $items[] = [
                        // General New
                        'price' => $env['PRICE_GENERAL_HALF'],
                        'quantity' => 1
                        ];
                    break;
                case 'senior':
                    $items[] = [
                        // Senior New
                        'price' => $env['PRICE_SENIOR_HALF'],
                        'quantity' => 1,
                      ];
                    break;
                case 'junior':
                    $items[] = [
                        // Junior New
                        'price' => $env['PRICE_JUNIOR_HALF'],
                        'quantity' => 1
                      ];
                    break;
                case 'archery':
                    $items[] = [
                        // General New
                        'price' => $env['PRICE_GENERAL_HALF'],
                        'quantity' => 1
                        ];
                    break;
                default:
                $items[] = [
                    // General New
                    'price' => $env['PRICE_GENERAL_HALF'],
                    'quantity' => 1
                    ];
            }
            break;
        case 'renew':
            switch($membershipFee){
                case 'general':
                    $items[] = [
                        // General New
                        'price' => $env['PRICE_GENERAL'],
                        'quantity' => 1
                        ];
                    break;
                case 'senior':
                    $items[] = [
                        // Senior New
                        'price' => $env['PRICE_SENIOR'],
                        'quantity' => 1,
                      ];
                    break;
                case 'junior':
                    $items[] = [
                        // Junior New
                        'price' => $env['PRICE_JUNIOR'],
                        'quantity' => 1
                      ];
                    break;
                case 'archery':
                    $items[] = [
                        // General New
                        'price' => $env['PRICE_GENERAL_HALF'],
                        'quantity' => 1
                        ];
                    break;
                default:
                $items[] = [
                    // General New
                    'price' => $env['PRICE_GENERAL'],
                    'quantity' => 1
                    ];
            }
            break;
        case 'update':
          break;
        default:
            $items = [[
                'price' => $env['PRICE_INITIATION'],
                'quantity' => 1
                ]];
            $items[] = [
                // General New
                'price' => $env['PRICE_GENERAL'],
                'quantity' => 1
                ];
      }
      
      return $items;
    }

    // Process applicants list early
    $enc_key = $_ENV['ENCRYPTION_KEY'] ?? null;
    $applicants_list = [];
    $stored_records = [];
    $applicant_uuids = [];

    if (!empty($_POST['members_json'])) {
      $payload = json_decode($_POST['members_json'], true);
      if (is_array($payload)) {
        // Support two shapes:
        // 1) array of applicant objects (added via member form addApplicant)
        // 2) legacy single-object shape { applicant: {...}, members: [...] }
        $applicants_list = $payload;        
      }
    } 

    // Build items for all applicants and prepare encryption
    $all_items = [];
    
    foreach ($applicants_list as $ap) {
      $appObj = is_array($ap) ? $ap : [];

      $afn = trim((string)($appObj['firstname'] ?? ''));
      $aln = trim((string)($appObj['lastname'] ?? ''));
      $aem = trim((string)($appObj['email'] ?? ''));

      if ($afn === '' || $aln === '' || $aem === '') {
        continue; // Skip invalid applicants
      }

      // Generate UUID for this applicant
      $app_uuid = uuidv4();
      $applicant_uuids[] = $app_uuid;

      // Build items for this applicant
      $appType = $appObj['applicationType'] ?? 'new';
      $membershipFee = $appObj['membershipFee'] ?? 'general';
      $family = (int)($appObj['familyCount'] ?? 0);
      $extra = (int)($appObj['extra'] ?? 0);      

      $applicant_items = buildItemsForApplicant($appType, $membershipFee, $_ENV);

      // Add family members items for this applicant
      if (isset($family) && $family != 0) {
        $applicant_items[] = [
          'price' => $_ENV['PRICE_FAMILY'],
          'quantity' => $family,
          'adjustable_quantity' => [
            'enabled' => true,
            'minimum' => 0,
            'maximum' => 10
          ]
        ];
      }

      // Add extra items for this applicant
      if (isset($extra) && $extra != 0) {
        $applicant_items[] = [
          'price' => $_ENV['PRICE_EXTRA'],
          'quantity' => $extra,
          'adjustable_quantity' => [
            'enabled' => true,
            'minimum' => 0,
            'maximum' => 10
          ]
        ];
      }

      // Add all items with metadata linking to applicant UUID
      foreach ($applicant_items as $item) {
        $all_items[] = $item;
      }
      

      // Encrypt and store applicant record (now including family array if present)
      if ($enc_key) {
        $app_json = json_encode($appObj);
        $enc = encrypt_record($app_json, $enc_key);
        if ($enc !== false) {
          $record = [
            'uuid' => $app_uuid,
            'type' => 'applicant',
            'data' => $enc,
            'created_at' => date('Y-m-d H:i:s')
          ];
          $stored_records[] = $record;
        }
      }
    }

    // Build metadata with only UUIDs
    $meta = [];
    if (!empty($applicant_uuids)) {
      $meta['applicant_uuids'] = implode(',', $applicant_uuids);
      $meta['total_applicants'] = count($applicant_uuids);
    }
  
    // Create checkout session with all applicants' items
    $checkout_session = $stripe->checkout->sessions->create([
      'success_url' => $domain_url . $_ENV['SUCCESS_PATH'],
      'cancel_url' => $domain_url . $_ENV['CANCEL_PATH'],
      'mode' => 'payment',
      'automatic_tax' => ['enabled' => false],
      'line_items' => $all_items,
      'metadata' => $meta
    ]);    

    // Store encrypted applicant records if encryption key is available
    if ($enc_key && !empty($stored_records)) {

      
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost:3306';
    $dbName = $_ENV['DB_NAME'] ?? null;
    $dbUser = $_ENV['DB_USER'] ?? null;
    $dbPass = $_ENV['DB_PASS'] ?? null;
   /*
    $dbHost = 'localhost:3306';
    $dbName = 'pfga_forum';
    $dbUser = 'root';
    $dbPass = '1q2w3e4r';
 */
      // Prepare DB connection if possible
      $mysqli = null;
      if ($dbName && $dbUser) {
        $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($mysqli->connect_errno) {
          error_log('DB connect failed: ' . $mysqli->connect_error);
          $mysqli = null;
        }
      } else {
        error_log('DB credentials not set; cannot write encrypted records to DB.');
      }

      $stmt = null;
      if ($mysqli) {
        $stmt = $mysqli->prepare("INSERT INTO encrypted_members (uuid, `type`, data, created_at) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
          error_log('Failed to prepare DB statement: ' . $mysqli->error);
        }
      }

      // Write stored records into DB
      if ($stmt) {
        foreach ($stored_records as $rec) {
          $stmt->bind_param('ssss', $rec['uuid'], $rec['type'], $rec['data'], $rec['created_at']);
          $stmt->execute();
        }
        $stmt->close();
      }

      if ($mysqli) {
        $mysqli->close();
      }
    }

    header("HTTP/1.1 303 See Other");
    header("Location: " . "$checkout_session->url");
  }
  }
