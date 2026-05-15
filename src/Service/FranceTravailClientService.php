<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FranceTravailClientService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $clientId,
        private string $clientSecret,
        private string $tokenUrl,
        private string $apiBaseUrl,
    ) {
    }

    public function getAccessToken(): string
    {
        $response = $this->httpClient->request('POST', $this->tokenUrl, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                // selon l'API ciblée, un scope peut être requis
            ],
        ]);

        $data = $response->toArray();

        if (!isset($data['access_token'])) {
            throw new \RuntimeException('Token France Travail introuvable.');
        }

        return $data['access_token'];
    }

    public function searchOffers(array $query = []): array
    {
        $token = $this->getAccessToken();

        $response = $this->httpClient->request(
            'GET',
            $this->apiBaseUrl . '/partenaire/offresdemploi/v2/offres/search',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'query' => $query,
            ]
        );

        return $response->toArray();
    }
}