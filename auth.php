<?php

declare(strict_types=1);

use Barryvdh\WeheatProxy\WeheatApi;

require_once('vendor/autoload.php');

$climate = new League\CLImate\CLImate;
$climate->out('Please provide your WeHeat credentials so we can request a refresh token.');
$username = $climate->input('Username:')->prompt();
$password = $climate->password('Password:')->prompt();

$offline = $climate->confirm(
    'Do you want to generate an offline token without expiration? Otherwise the token will expire in 30 days'
);
$api = new WeheatApi();

try {
    $api->authenticate($username, $password, $offline->confirmed());
} catch (\Exception $e) {
    $climate->error($e->getMessage());
    exit(1);
}

$climate->out('Refresh token:');
$climate->bold($api->getRefreshToken());

$data = $climate->confirm('Do you want to show your heatpumps?');
if ($data->confirmed()) {
    $climate->dump($api->getHeatpumps());
}