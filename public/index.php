<?php

require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="WeHeat Credentials"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Please login using your WeHeat credentials';
    exit;
}

$api = new \Barryvdh\WeheatProxy\WeheatApi();

$api->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

$response = $api->makeRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_GET ?? [], $_POST ?? null);

header('Content-Type: application/json');
echo $response->getBody()->getContents();