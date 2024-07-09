<?php

require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="WeHeat Credentials"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Please login using your WeHeat credentials';
    exit;
}

$api = new \Barryvdh\WeheatProxy\WeheatApi();

$username = $_SERVER['PHP_AUTH_USER'];
if (str_starts_with($username, 'ey') && empty($_SERVER['PHP_AUTH_PW'])) {
    $api->refreshToken($username);
} else {
    $api->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}

$requestUri = $_SERVER['REQUEST_URI'];

// For scripts containing index.php, use path after.
if (str_contains($requestUri, '/index.php/')) {
    list($script, $requestUri) = explode('/index.php/', $requestUri, 2);
}

$response = $api->makeRequest($_SERVER['REQUEST_METHOD'], $requestUri, $_GET ?? [], $_POST ?? null);

header('Content-Type: application/json');
echo $response->getBody()->getContents();