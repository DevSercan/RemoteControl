<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once 'config.php';

function validateApiKey() {
    $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
    
    if (!$authorizationHeader) {
        sendError(401, "Authorization header not found");
    }

    if (strpos($authorizationHeader, 'Bearer ') !== 0) {
        sendError(400, "Authorization header must be in Bearer token format");
    }

    $apiKey = substr($authorizationHeader, 7); // "Bearer " kısmını çıkar
    if ($apiKey !== API_KEY) {
        sendError(401, "Invalid API Key");
    }
}

function sendError($statusCode, $message) {
    header("HTTP/1.1 $statusCode");
    echo json_encode(["error" => $message]);
    exit;
}

validateApiKey();

$requestUri = $_SERVER["REQUEST_URI"];
$requestMethod = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"), true);

$endpoints = [
    '/api/data' => 'handleData',
    '/api/command' => 'handleCommand'
];

foreach ($endpoints as $endpoint => $handler) {
    if (strpos($requestUri, $endpoint) === 0) {
        $handler($requestMethod, $input);
        exit;
    }
}

sendError(404, "Invalid endpoint");

function handleData($method, $input) {
    handleFileOperation($method, $input, DATA_FILE, "data");
}

function handleCommand($method, $input) {
    handleFileOperation($method, $input, COMMAND_FILE, "command");
}

function handleFileOperation($method, $input, $filePath, $key) {
    switch ($method) {
        case "GET":
            if (!file_exists($filePath) || filesize($filePath) === 0) {
                echo json_encode(new stdClass());
            } else {
                $content = file_get_contents($filePath);
                echo $content ?: json_encode(new stdClass());
            }
            break;
        case "POST":
        case "PUT":
            if (isset($input[$key])) {
                file_put_contents($filePath, json_encode([$key => $input[$key]]));
                echo json_encode(["message" => ucfirst($method) . " successful"]);
            } else {
                sendError(400, "No $key provided");
            }
            break;
        case "DELETE":
            if (file_exists($filePath)) unlink($filePath);
            echo json_encode(["message" => "$key deleted"]);
            break;
        default:
            sendError(405, "Method Not Allowed");
    }
}
