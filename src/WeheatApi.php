<?php

declare(strict_types=1);

namespace Barryvdh\WeheatProxy;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

class WeheatApi
{
    protected $clientId = 'WeheatCommunityAPI';

    protected $tokenUrl = 'https://auth.weheat.nl/auth/realms/Weheat/protocol/openid-connect/token';

    protected $baseUrl = 'https://api.weheat.nl/';

    private $token;

    public function authenticate(string $username, string $password, bool $offline = false): void
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->tokenUrl, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'scope' => $offline ? 'openid offline_access' : 'openid',
                'username' => $username,
                'password' => $password,
            ]
        ]);

        $this->token = json_decode($res->getBody()->getContents(), true);
    }

    public function refreshToken(string $refreshToken): void
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->tokenUrl, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'refresh_token' => $refreshToken,
            ]
        ]);

        $this->token = json_decode($res->getBody()->getContents(), true);
    }

    public function getToken(): array
    {
        if (!isset($this->token['access_token'])) {
            throw new \RuntimeException('Not Authenticated');
        }

        return $this->token;
    }

    public function setToken(array $token): void
    {
        if (!isset($token['access_token'])) {
            throw new \InvalidArgumentException('Invalid token');
        }

        $this->token = $token;
    }

    public function getAccessToken(): string
    {
        if (!isset($this->token['access_token'])) {
            throw new \RuntimeException('Not Authenticated');
        }

        return $this->token['access_token'];
    }

    public function getRefreshToken(): string
    {
        if (!isset($this->token['refresh_token'])) {
            throw new \RuntimeException('Not Authenticated');
        }

        return $this->token['refresh_token'];
    }

    public function makeRequest($method, $path, $query = null, $params = null): Response
    {
        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request($method, $this->baseUrl . ltrim($path, '/'), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'query' => $query,
                'form_params' => $params,
            ]);
        } catch (ClientException $clientException) {
            dump($clientException->getResponse()->getBody()->getContents());
            throw $clientException;
        }

        return $res;
    }

    public function getHeatpumps(): array
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps');

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getHeatpumpData(string $heatpumpId): array
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps/' . $heatpumpId);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getHeatpumpLatestLogs(string $heatpumpId): array
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps/' . $heatpumpId . '/logs/latest');

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getHeatpumpLogs(string $heatpumpId, string $startTime, string $endTime, string $interval): array
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps/' . $heatpumpId . '/logs', [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'interval' => $interval
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

}