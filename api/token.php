<?php
/**
 * ChatModel OAuth 2.0 Token Endpoint
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
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'Only POST method is supported']);
    exit;
}

echo json_encode([
    'token_type' => 'Bearer',
    'expires_in' => 3600,
    'access_token' => bin2hex(random_bytes(32)),
    'scope' => 'chat'
]);
