<?php
/**
 * Membership Fees Configuration
 * Outputs membership fee constants from environment variables as JavaScript
 * This file is included inline in memberform.php to make fees available to member.js
 */

require_once 'shared.php';

// Get fee amounts from environment variables with sensible defaults
$fees = [
    'general' => (int)($_ENV['PRICE_GENERAL_AMOUNT'] ?? 350),
    'senior' => (int)($_ENV['PRICE_SENIOR_AMOUNT'] ?? 320),
    'junior' => (int)($_ENV['PRICE_JUNIOR_AMOUNT'] ?? 250),
    'halfGeneral' => (int)($_ENV['PRICE_GENERAL_HALF_AMOUNT'] ?? 250),
    'halfSenior' => (int)($_ENV['PRICE_SENIOR_HALF_AMOUNT'] ?? 230),
    'halfJunior' => (int)($_ENV['PRICE_JUNIOR_HALF_AMOUNT'] ?? 180),
    'initiation' => (int)($_ENV['PRICE_INITIATION_AMOUNT'] ?? 75),
    'extraCards' => (int)($_ENV['PRICE_EXTRA_AMOUNT'] ?? 25),
    'family' => (int)($_ENV['PRICE_FAMILY_AMOUNT'] ?? 20),
];

// Output as JavaScript global object
echo "window.MEMBERSHIP_FEES = " . json_encode($fees) . ";";
?>
