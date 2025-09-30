<?php
// currency_api.php

header('Content-Type: application/json');

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$amount = $_GET['amount'] ?? '';

if (!$from || !$to || !$amount) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit;
}

// Use exchangerate.host (free API)
$apiUrl = "https://api.exchangerate.host/convert?from=" . urlencode($from) . "&to=" . urlencode($to) . "&amount=" . urlencode($amount);

$response = @file_get_contents($apiUrl);
if ($response === FALSE) {
    echo json_encode(["success" => false, "error" => "API request failed"]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['result'])) {
    echo json_encode(["success" => false, "error" => "Invalid API response"]);
    exit;
}

echo json_encode([
    "success" => true,
    "rate" => $data['info']['rate'],
    "converted" => $data['result']
]);

