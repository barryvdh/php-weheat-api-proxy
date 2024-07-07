<?php
declare(strict_types=1);

namespace Barryvdh\WeheatProxy;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

class WeheatApi {

    protected $clientId = 'WeheatCommunityAPI';

    protected $tokenUrl = 'https://auth.weheat.nl/auth/realms/Weheat/protocol/openid-connect/token';

    protected $baseUrl = 'https://api.weheat.nl/';
    protected $token;
    public function authenticate($username, $password)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->tokenUrl, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'scope' => 'openid',
                'username' => $username,
                'password' => $password,
            ]
        ]);

        $this->token = json_decode($res->getBody()->getContents(), true);
    }

    public function makeRequest($method, $path, $query = null, $params = null): Response
    {
        if (!isset($this->token['access_token'])) {
            throw new \RuntimeException('Not Authenticated');
        }

        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request($method, 'https://api.weheat.nl/' . ltrim($path, '/'), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token['access_token'],
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

    public function getHeatpumps()
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps');

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getHeatpumpData($heatpumpId)
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps/' . $heatpumpId);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getHeatpumpLatestLogs($heatpumpId)
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps/' . $heatpumpId .'/logs/latest');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $heatpumpId
     * @param $startTime
     * @param $endTime
     * @param $interval Minute, FiveMinute, FifteenMinute, Hour, Day, Week, Month, Year
     * @return mixed
     */
    public function getHeatpumpLogs($heatpumpId, $startTime, $endTime, $interval)
    {
        $response = $this->makeRequest('GET', 'api/v1/heat-pumps/' . $heatpumpId . '/logs', [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'interval' => $interval
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

}