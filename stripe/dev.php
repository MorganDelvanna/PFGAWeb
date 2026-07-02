<?php
session_start();
// Import the third party libraries, including stripe-php, which are managed by
// composer.
require '../vendor/autoload.php';

// If the .env file was not configured properly, display a helpful message.
if(!file_exists('../.dev')) {
  http_response_code(400);
  ?>
  <p>Make a copy of <code>.env.example</code>, place it in the same directory as composer.json, and name it <code>.env</code>, then populate the variables.</p>
  <p>It should look something like the following, but contain your <a href='https://dashboard.stripe.com/test/apikeys'>API keys</a>:</p>
  <pre>STRIPE_PUBLISHABLE_KEY=pk_test...
STRIPE_SECRET_KEY=sk_test...
STRIPE_WEBHOOK_SECRET=whsec_...
PRICE=price_abc123...
DOMAIN=http://localhost:4242</pre>
  <hr>

  <p>You can use this command to get started:</p>
  <pre>cp .env.example .env</pre>

  <?php
  exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.dev');
$dotenv->load();

// Make sure the configuration file is good.
if (!$_ENV['STRIPE_SECRET_KEY']) {
  http_response_code(400);
  ?>

  <h1>Invalid <code>.env</code></h1>
  <p>Make a copy of <code>.env.example</code>, place it in the same directory as composer.json, and name it <code>.env</code>, then populate the variables.</p>
  <p>It should look something like the following, but contain your <a href='https://dashboard.stripe.com/test/apikeys'>API keys</a>:</p>
  <pre>STRIPE_PUBLISHABLE_KEY=pk_test...
STRIPE_SECRET_KEY=sk_test...
STRIPE_WEBHOOK_SECRET=whsec_...
PRICE=price_abc123...
DOMAIN=http://localhost:4242</pre>
  <hr>

  <hr>

  <p>You can use this command to get started:</p>
  <pre>cp .env.example .env</pre>

  <?php
  exit;
}

// This sample requires that a Stripe Price was created. You can create a
// Product and Price from the Stripe dashboard, or using the Stripe CLI.
//
// https://stripe.com/docs/api/prices/create
$price = $_ENV['PRICE_GENERAL'];
if (!$price || $price == 'price_12345...') {
  http_response_code(400);
  ?>

  <h1>Missing price ID in <code>.env</code></h1>
  <p>Make a copy of <code>.env.example</code>, place it in the same directory as composer.json, and name it <code>.env</code>, then populate the variables.</p>
  <p>It should look something like the following, but contain your <a href='https://dashboard.stripe.com/test/apikeys'>API keys</a>:</p>
  <pre>STRIPE_PUBLISHABLE_KEY=pk_test...
STRIPE_SECRET_KEY=sk_test...
STRIPE_WEBHOOK_SECRET=whsec_...
PRICE=price_abc123...
DOMAIN=http://localhost:4242</pre>
  <hr>

  <p>You can use this command to <a href="https://stripe.com/docs/api/prices/create">create a price</a> with the Stripe CLI:</p>
  <pre>stripe prices create --unit-amount 1000 --currency usd</pre>

  <?php
  exit;
}

// For sample support and debugging. Not required for production:
\Stripe\Stripe::setAppInfo(
  "pfga_checkout",
  "0.0.1",
  ""
);

$stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

// Retrieve all encrypted member records from DB, decrypt and output as JSON
header('Content-Type: application/json; charset=utf-8');

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['DB_NAME'] ?? null;
$dbUser = $_ENV['DB_USER'] ?? null;
$dbPass = $_ENV['DB_PASS'] ?? null;

$result = ['success' => false, 'records' => [], 'error' => null];

if (!$dbName || !$dbUser) {
  http_response_code(500);
  $result['error'] = 'DB credentials not configured; cannot retrieve encrypted records.';
  echo json_encode($result, JSON_PRETTY_PRINT);
  exit;
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
  http_response_code(500);
  $result['error'] = 'DB connect failed: ' . $mysqli->connect_error;
  echo json_encode($result, JSON_PRETTY_PRINT);
  exit;
}

// decryption helper (matches success.php implementation)
function decrypt_record($b64, $key) {
  $raw = base64_decode($b64);
  if ($raw === false || strlen($raw) < 17) return false;
  $iv = substr($raw, 0, 16);
  $cipher = substr($raw, 16);
  $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
  return $plain === false ? false : $plain;
}

$enc_key = $_ENV['ENCRYPTION_KEY'] ?? null;
// If encryption key not present in .dev, try loading the production .thing file
if (!$enc_key && file_exists(__DIR__ . '/..' . '/.thing')) {
  try {
    $dotenv2 = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.thing');
    $dotenv2->load();
    $enc_key = $_ENV['ENCRYPTION_KEY'] ?? $enc_key;
  } catch (Exception $e) {
    // ignore; we'll report missing key below
  }
}

if (!$enc_key) {
  // include a warning in the JSON so caller sees why decrypted values are null
  $result['warning'] = 'ENCRYPTION_KEY not set; decrypted fields will be null.';
}

$stmt = $mysqli->prepare("SELECT uuid, `type`, data, created_at, emailed, emailed_at FROM encrypted_members ORDER BY created_at DESC");
if (!$stmt) {
  http_response_code(500);
  $result['error'] = 'DB prepare failed: ' . $mysqli->error;
  echo json_encode($result, JSON_PRETTY_PRINT);
  $mysqli->close();
  exit;
}

if (!$stmt->execute()) {
  http_response_code(500);
  $result['error'] = 'DB execute failed: ' . $stmt->error;
  echo json_encode($result, JSON_PRETTY_PRINT);
  $stmt->close();
  $mysqli->close();
  exit;
}

$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $decrypted = null;
  if ($enc_key) {
    $plain = decrypt_record($row['data'], $enc_key);
    if ($plain !== false) {
      $decoded = json_decode($plain, true);
      $decrypted = $decoded === null ? $plain : $decoded;
    } else {
      $decrypted = null;
    }
  }

  $result['records'][] = [
    'uuid' => $row['uuid'],
    'type' => $row['type'],
    'created_at' => $row['created_at'],
    'emailed' => $row['emailed'],
    'emailed_at' => $row['emailed_at'],
    'decrypted' => $decrypted
  ];
}

$stmt->close();
$mysqli->close();

$result['success'] = true;
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
