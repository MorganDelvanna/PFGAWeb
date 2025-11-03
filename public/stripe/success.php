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


function preg_grep_keys($pattern, $input) {
  foreach ($input as $key => $value) {
    echo ' '. $key .' '. $value .' '.$pattern;
    if ($pattern == substr($key, 0, strlen($pattern))) {
            echo $value;
    }
  }
}

$meta = $checkout_session['metadata'];
switch( $meta['applicationType'] ) {
  case 'new':
    $appType = 'New';
    break;
  case 'half':
    $appType = 'New Half-Year';
    break;
  case 'renew':
    $appType = 'Renewal';
    break;
  default:
    $appType = 'New';
}
switch( $meta['membershipFeeType']){
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
  default:
    $feeType = 'General Membership';
}

$firstName = $meta['firstname'];
$lastName = $meta['lastname'];
$dob = $meta['dob'];
$address = $meta['address'].', '.$meta['city'].', '.$meta['province'].', '.$meta['postal']; 
$email = $meta['email'];
$disciplines = $meta['disciplines'];
$extraCards = $meta['extra'];

$customer = $checkout_session['customer_details'];
$ccEmail = $customer['email'];

$bodyHTML = "<strong>$appType $feeType</strong><br />";
$bodyHTML .= (strlen($meta['card']) > 0) ? '<strong>Card #</strong>: '.$meta['card'].'<br />' : '';   
$bodyHTML .= "<strong>First Name</strong>: $firstName<br />";
$bodyHTML .= "<strong>Last Name</strong>: $lastName<br />"; 
$bodyHTML .= (strlen($meta['alias']) > 0) ? '<strong>Preferred Name</strong>: '.$meta['alias'].'<br />' : '';
$bodyHTML .= "<strong>Date of Birth</strong>: $dob<br />";  
$bodyHTML .= "<strong>Address</strong>: $address<br />";  
$bodyHTML .= (strlen($meta['homephone']) > 0) ? '<strong>Home Phone</strong>: '.$meta['homephone'].'<br />':'';  
$bodyHTML .= (strlen($meta['cellphone']) > 0) ? '<strong>Cell Phone</strong>: '.$meta['cellphone'].'<br />':'';
$bodyHTML .= "<strong>Email</strong>: $email<br />";
$bodyHTML .= ($meta['palType']=='noPal') ? 'None<br />' : "<strong>PAL</strong>: ".$meta['palNum'].' expires: '.$meta['palExpiry'].'<br />' ;  
$bodyHTML .= (strlen($meta['palDate'])>0) ? '<strong>Approx PAL Date</strong>: '.$meta['palDate'].'<br />' :''; 
$bodyHTML .= "<strong>Disciplines</strong>: $disciplines<br />";
$bodyHTML .= "<strong>Extra Cards Ordered</strong>: $extraCards<br />";  
if ($meta['family'] > 0) {
  $bodyHTML .= '<p><strong>Family Members</strong><br/>';
  for ($i = 0; $i < (int)$meta['family']; $i++){
    $bodyHTML .= $meta['familyMember'.$i].'<br />';
  };
  $bodyHTML .= "</p>";
};
if(isset($meta['club0'])){
  $bodyHTML .= '<p><strong>Club Affiliations</strong><br />';
  for ($i = 0; $i < 20; $i++){
    if(isset($meta['club'.$i])){
      $bodyHTML .= $meta['club'.$i].'';
    }
  };
  $bodyHTML .= "</p>";
};
if(isset($meta['course0'])){
  $bodyHTML .= '<strong>Course/Training</strong><br />';
  for ($i = 0; $i < 20; $i++){
    if(isset($meta['course'.$i])){
      $bodyHTML .= $meta['course'.$i].'';
    }
  };
  $bodyHTML .= "</p>";
};


$bodyText = "$appType $feeType\n";
$bodyText .= (strlen($meta['card']) > 0) ? 'Card #: '.$meta['card'].'\n' : '';   
$bodyText .= "First Name: $firstName\n";
$bodyText .= "Last Name: $lastName\n"; 
$bodyText .= (strlen($meta['alias']) > 0) ? 'Preferred Name: '.$meta['alias'].'\n' : '';
$bodyText .= "Date of Birth: $dob\n";  
$bodyText .= "Address: $address\n";  
$bodyText .= (strlen($meta['homephone']) > 0) ? 'Home Phone: '.$meta['homephone'].'\n':'';  
$bodyText .= (strlen($meta['cellphone']) > 0) ? 'Cell Phone: '.$meta['cellphone'].'\n':'';
$bodyText .= "Email: $email\n";
$bodyText .= ($meta['palType']=='noPal') ? 'None' : "PAL: ".$meta['palNum'].' <strong>Expires:</strong> '.$meta['palExpiry']."\n";  
$bodyText .= (strlen($meta['palDate'])>0) ? 'Approx PAL Date: '.$meta['palDate']."\n" :''; 
$bodyText .= "Disciplines: $disciplines\n";
$bodyText .= "Extra Cards Ordered: $extraCards\n";  
if ($meta['family'] > 0) {
  $bodyText .= 'Family Members<br/>';
  for ($i = 0; $i < (int)$meta['family']; $i++){
    $bodyText .= $meta['familyMember'.$i].'\n';
  };
  $bodyText .= "";
};
if(isset($meta['club0'])){
  $bodyText .= 'Club Affiliations\n';
  for ($i = 0; $i < 20; $i++){
    if(isset($meta['club'.$i])){
      $bodyText .= $meta['club'.$i].'';
    }
  };
  $bodyText .= "";
};
if(isset($meta['course0'])){
  $bodyText .= 'Course/Training\n';
  for ($i = 0; $i < 20; $i++){
    if(isset($meta['course'.$i])){
      $bodyText .= $meta['course'.$i].'';
    }
  };
  $bodyText .= "";
};

$mail = new PHPMailer();
// Settings
$mail->isSMTP();
$mail->CharSet = 'UTF-8';
$mail->Host = $_ENV['HOST'];
$mail->SMTPDebug = 0;
$mail->SMTPAuth = True;
$mail->Port = $_ENV['PORT'];
$mail->Username = $_ENV['USERNAME'];
$mail->Password = $_ENV['PASSWORD'];

// Content
$mail->setFrom($_ENV['FROM']);
$mail->addAddress($_ENV['FROM']);
$mail->AddCC($ccEmail);
$mail->isHTML(true);
$mail->Subject = "PFGA $appType from Stripe";
$mail->Body = "Hello Membership Secretary,<br />".$bodyHTML."Thanks,<br />The PFGA Stripe Application";
$mail->AltBody = "Hello Membership Secretary,\n".$bodyText."Thanks,\nThe PFGA Stripe Application";;

if(!$mail->Send()) {
   $arrResult['response'] = 'error';
   echo "There was a problem sending the form.: " . $mail->ErrorInfo;
   exit;
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
        <div class="col-12">
            <div id="menu">

            </div>
        </div>
    </div>
    <div class="sr-root">
      <div class="sr-main">
        <div class="sr-payment-summary completed-view">
          <h1>Your payment succeeded</h1>
        </div>
        <p>
          <?php 
          if($meta['applicationType'] == 'renew'){ echo 'You have successfully renewed, do not send in another form of payment. The following has been emailed to the Membership Secretary and to you:'; } 
          else { echo 'Your application has been submitted and will be considered at the next Board of Directors meeting. <br>You will be contacted sometime after the meeting to inform you if you have been accepted or not.<br>The following has been emailed to the Membership Secretary and to you:'; }
          ?>
        </p>
        
          <div class="sr-section completed-view">
            <!--<pre><?= json_encode($checkout_session['metadata'], JSON_PRETTY_PRINT); ?></pre>-->
            <?php echo $bodyHTML ?>
          </div>
          <div class="sr-section">
            <?php 
              if($meta['applicationType'] != 'renew'){ echo 'As part of the new member application process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca. The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured, background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. '; }
            ?>
          </div>
        </div>
      </div>
    </div>
    <input type="hidden" id="pageRef" value="#l_membership" />
    <script src="../js/bootstrap.js" type="text/javascript"></script>
    <script src="../js/common.js" type="text/javascript"></script>
  </body>
</html>
