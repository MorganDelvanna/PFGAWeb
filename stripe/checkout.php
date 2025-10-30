<?php
require_once 'shared.php';

$domain_url = $_ENV['DOMAIN'];

if (!empty($_POST['token'])) {
  if (hash_equals($_SESSION['token'], $_POST['token'])) {
    
    $items = [];
    $applicationType = $_POST['applicationType'];
    $membershipFee = $_POST['membershipFee'];
    
    switch ($applicationType){
        case ('new'):
            $items = [[
                'price' => $_ENV['PRICE_INITIATION'],
                'quantity' => 1
                ]];
            switch($membershipFee){
                case 'general':
                    $items[] = [
                        // General New
                        'price' => $_ENV['PRICE_GENERAL'],
                        'quantity' => 1
                        ];
                    break;
                case 'senior':
                    $items[] = [
                        // Senior New
                        'price' => $_ENV['PRICE_SENIOR'],
                        'quantity' => 1,
                      ];
                    break;
                case 'junior':
                    $items[] = [
                        // Junior New
                        'price' => $_ENV['PRICE_JUNIOR'],
                        'quantity' => 1
                      ];
                    break;
                default:
                $items[] = [
                    // General New
                    'price' => $_ENV['PRICE_GENERAL'],
                    'quantity' => 1
                    ];
            }
            break;
        case 'half':
            $items = [[
                'price' => $_ENV['PRICE_INITIATION'],
                'quantity' => 1
                ]];
            switch($membershipFee){
                case 'general':
                    $items[] = [
                        // General New
                        'price' => $_ENV['PRICE_GENERAL_HALF'],
                        'quantity' => 1
                        ];
                    break;
                case 'senior':
                    $items[] = [
                        // Senior New
                        'price' => $_ENV['PRICE_SENIOR_HALF'],
                        'quantity' => 1,
                      ];
                    break;
                case 'junior':
                    $items[] = [
                        // Junior New
                        'price' => $_ENV['PRICE_JUNIOR_HALF'],
                        'quantity' => 1
                      ];
                    break;
                case 'archery':
                    $items[] = [
                        // General New
                        'price' => $_ENV['PRICE_GENERAL_HALF'],
                        'quantity' => 1
                        ];
                    break;
                default:
                $items[] = [
                    // General New
                    'price' => $_ENV['PRICE_GENERAL_HALF'],
                    'quantity' => 1
                    ];
            }
            break;
        case 'renew':
            switch($membershipFee){
                case 'general':
                    $items[] = [
                        // General New
                        'price' => $_ENV['PRICE_GENERAL'],
                        'quantity' => 1
                        ];
                    break;
                case 'senior':
                    $items[] = [
                        // Senior New
                        'price' => $_ENV['PRICE_SENIOR'],
                        'quantity' => 1,
                      ];
                    break;
                case 'junior':
                    $items[] = [
                        // Junior New
                        'price' => $_ENV['PRICE_JUNIOR'],
                        'quantity' => 1
                      ];
                    break;
                case 'archery':
                    $items[] = [
                        // General New
                        'price' => $_ENV['PRICE_GENERAL_HALF'],
                        'quantity' => 1
                        ];
                    break;
                default:
                $items[] = [
                    // General New
                    'price' => $_ENV['PRICE_GENERAL'],
                    'quantity' => 1
                    ];
            }
            break;
        default:
            $items = [[
                'price' => $_ENV['PRICE_INITIATION'],
                'quantity' => 1
                ]];
            $items[] = [
                // General New
                'price' => $_ENV['PRICE_GENERAL'],
                'quantity' => 1
                ];
    }
    
    $family = $_POST['family'];
      if(isset($family) && $family != 0) {
        $items[] = [ //Additional Family Members
          'price' => $_ENV['PRICE_FAMILY'],
          'quantity' => (int)$family,
          'adjustable_quantity' => [
            'enabled' => true,
            'minimum' => 0,
            'maximum' => 10
          ]
        ];
      }
    
      $extra = $_POST['extra'];
      if(isset($extra) && $extra != 0) {
        $items[] = [ //Additional Family Members
          'price' => $_ENV['PRICE_EXTRA'],
          'quantity' => (int)$extra,
          'adjustable_quantity' => [
            'enabled' => true,
            'minimum' => 0,
            'maximum' => 10
          ]
        ];
      }
    
    
    
      $disciplines = [];
    
      if(isset($_POST['archery'])){
        $disciplines[] = 'archery';  
      };
      if(isset($_POST['rifle'])){
        $disciplines[] = 'rifle';
      };
      if(isset($_POST['smallbore'])){
        $disciplines[] = 'smallbore';
      };
      if(isset($_POST['handgun'])){
        $disciplines[] = 'handgun';
      };
      if(isset($_POST['action'])){
        $disciplines[] = 'action';
      };
      if(count($disciplines) > 0){
        $disciplineString = (string)implode(', ', $disciplines);
      } else { $disciplineString = "None"; };
  
      $terms = isset($_POST['terms']) ? 'true' : 'false';      
    
      $meta = [
        'applicationType' => $applicationType,
        'firstname' => $_POST['firstname'],
        'lastname' => $_POST['lastname'],
        'alias' => $_POST['alias'],
        'dob' => $_POST['dob'],
        'card' => $_POST['card'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'province' => $_POST['province'],
        'postal' => $_POST['postal'],
        'homephone' => $_POST['homephone'],
        'cellphone' => $_POST['cellphone'],
        'email' => $_POST['email'],
        'palType' => $_POST['palType'],
        'palDate' => $_POST['palDate'],
        'palNum' => $_POST['palNum'],
        'palExpiry' => $_POST['palExpiry'],
        'disciplines' => $disciplineString,
        'membershipFeeType' => $_POST['membershipFee'],
        'family' => (string)$_POST['family'],
        'extra' => (string)$_POST['extra'],
        'terms' => $terms
      ];
    
      if(isset($_POST['familyMembers'])){
        $count = 0;
        foreach ($_POST['familyMembers'] as $familyMember){
        
          $newKey = 'familyMember'.$count;
          $meta[$newKey] = (string)$familyMember; 
          $count++;
        }
      };
    
      if(isset($_POST['otherClubs'])){
        $count = 0;
        foreach ($_POST['otherClubs'] as $club){
          $newKey = 'club'.$count;
          $meta[$newKey] = (string)$club;
          $count++;
        }
      };
    
      if(isset($_POST['courses'])){
          $count = 0;
          foreach ($_POST['courses'] as $course){
            $newKey = 'course'.$count;
            $meta[$newKey] = (string)$course;
            $count++;
          }
      };
      

      $checkout_session = $stripe->checkout->sessions->create([
        'success_url' => $domain_url . $_ENV['SUCCESS_PATH'],
        'cancel_url' => $domain_url . $_ENV['CANCEL_PATH'],
        'mode' => 'payment',
        'automatic_tax' => ['enabled' => false],
        'line_items' => $items,
        'metadata' => $meta
      ]);

      header("HTTP/1.1 303 See Other");
      header("Location: " . "$checkout_session->url");
      
  } else {
       echo "You are not Authorized";
  }
}

