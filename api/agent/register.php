<?php
/**
 * ChatModel Agent Registration Endpoint (Auth.md)
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?? [];

$agentId = 'agent_' . bin2hex(random_bytes(12));
$token = bin2hex(random_bytes(32));

echo json_encode([
    'status' => 'registered',
    'agent_id' => $agentId,
    'access_token' => $token,
    'token_type' => 'Bearer',
    'expires_in' => 86400,
    'claim_uri' => 'https://chatmodel.in/api/agent/claim.php'
]);
