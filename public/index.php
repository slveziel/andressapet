<?php
/**
 * Andressa Pet - Entry Point
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../app/config.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// API Routes
if (strpos($uri, '/api/') === 0) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/../app/controllers/ApiController.php';
    
    $path = substr($uri, 5);
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $controller = new ApiController();
    $result = $controller->handle($method, $path, $data);
    
    $code = $result['code'] ?? 200;
    http_response_code($code);
    
    if (isset($result['error'])) {
        echo json_encode(['error' => $result['error']]);
    } else {
        echo json_encode($result['data']);
    }
    exit;
}

// Arquivos estáticos
$staticFile = __DIR__ . $uri;
if ($uri !== '/' && file_exists($staticFile) && is_file($staticFile)) {
    $ext = pathinfo($staticFile, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($staticFile);
    exit;
}

include __DIR__ . '/index.html';
