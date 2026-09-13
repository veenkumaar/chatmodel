<?php
/**
 * ChatModel Platform API Health Status
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'healthy',
    'timestamp' => date('c'),
    'service' => 'ChatModel Conversational API',
    'version' => '1.0.0'
]);
