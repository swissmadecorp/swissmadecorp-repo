<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UspsService
{
    protected $client;
    protected $clientId;
    protected $clientSecret;
    // Tip: Use https://apis-tem.usps.com/ for sandbox/testing
    protected $baseUri = 'https://apis.usps.com/';

    public function __construct()
    {
        $this->clientId = config('usps.customer_key');
        $this->clientSecret = config('usps.secret_key');

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 10,
        ]);
    }

    protected function getAccessToken()
    {
        return Cache::remember('usps_access_token', 3500, function () {
            try {
                $response = $this->client->post('oauth2/v3/token', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ],
                    'json' => [
                        'client_id'     => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'grant_type'    => 'client_credentials',
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                return $data['access_token'];

            } catch (RequestException $e) {
                $error = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
                Log::error('USPS Auth Failed: ' . $error);
                throw $e;
            }
        });
    }

    public function getCityState($zipCode)
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = $this->client->get('addresses/v3/city-state', [
                'query' => ['ZIPCode' => $zipCode],
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            // Handle 200 OK but "Address Not Found" scenarios
            if (!isset($result['city'])) {
                return null;
            }

            $countries = new \App\Libs\Countries;
            $state = $countries->getStateByCode($result['state']);

            return [
                'city'    => (STRING) ucwords(strtolower($result['city'])),
                'state'   => $state,
                'zipCode' => $result['ZIPCode'] ?? $zipCode,
            ];

        } catch (RequestException $e) {
            Log::error('USPS LookUp Error: ' . ($e->hasResponse() ? $e->getResponse()->getBody() : $e->getMessage()));
            return null;
        }
    }
}