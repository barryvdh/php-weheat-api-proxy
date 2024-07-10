<?php

use Barryvdh\WeheatProxy\WeheatApi;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['HTTP_X_REFRESH_TOKEN'])) {
    print_r($_SERVER);
    header('WWW-Authenticate: Basic realm="WeHeat Credentials"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Please login using your WeHeat credentials';
    exit;
}

$cache = new FilesystemAdapter();
$api = new WeheatApi();

if (isset($_SERVER['HTTP_X_REFRESH_TOKEN'])) {
    $username = $_SERVER['HTTP_X_REFRESH_TOKEN'];
    $password = '';
} else {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
}

$cacheKey = 'token.'.sha1($username.$password);

$token = $cache->get($cacheKey, function(ItemInterface $item) use($api, $username, $password) {
    if (str_starts_with($username, 'ey') && empty($password)) {
        $api->refreshToken($username);
    } else {
        $api->authenticate($username, $password);
    }

    $token = $api->getToken();

    // 5 second safety window
    $item->expiresAfter($token['expires_in'] - 5);

    return $token;
});

$api->setToken($token);

$requestUri = $_SERVER['REQUEST_URI'];

// For scripts containing index.php, use path after.
if (str_contains($requestUri, '/index.php/')) {
    list($script, $requestUri) = explode('/index.php/', $requestUri, 2);
}

try {
    $response = $api->makeRequest($_SERVER['REQUEST_METHOD'], $requestUri, $_GET ?? [], $_POST ?? null);
} catch (\GuzzleHttp\Exception\ClientException $clientException) {
    if ($clientException->getResponse()->getStatusCode() === 401) {
        $cache->delete($cacheKey);
    }
    throw $clientException;
}

header('Content-Type: application/json');
echo $response->getBody()->getContents();