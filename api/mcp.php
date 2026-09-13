<?php
/**
 * ChatModel Model Context Protocol (MCP) Streamable Server Endpoint
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // SSE Stream endpoint for MCP or info
    echo json_encode([
        'jsonrpc' => '2.0',
        'result' => [
            'name' => 'chatmodel-mcp-server',
            'version' => '1.0.0',
            'status' => 'online'
        ]
    ]);
    exit;
}

$raw = file_get_contents('php://input');
$request = json_decode($raw, true) ?? [];
$method = $request['method'] ?? '';
$id = $request['id'] ?? 1;

if ($method === 'tools/list') {
    echo json_encode([
        'jsonrpc' => '2.0',
        'id' => $id,
        'result' => [
            'tools' => [
                [
                    'name' => 'send_chat_message',
                    'description' => 'Send a message to a ChatModel conversational AI assistant',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string', 'description' => 'Prompt / input message'],
                            'subdomain' => ['type' => 'string', 'description' => 'Target assistant subdomain']
                        ],
                        'required' => ['message']
                    ]
                ]
            ]
        ]
    ]);
    exit;
}

echo json_encode([
    'jsonrpc' => '2.0',
    'id' => $id,
    'result' => [
        'status' => 'success'
    ]
]);
