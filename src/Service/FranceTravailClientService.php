<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FranceTravailClientService
{
    private ?string $accessToken = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $clientId,
        private string $clientSecret,
        private string $tokenUrl,
        private string $apiBaseUrl,
        private string $scope = '',
    ) {
    }

    public function getAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        if ($this->scope !== '') {
            $body['scope'] = $this->scope;
        }

        $response = $this->httpClient->request('POST', $this->tokenUrl, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query($body, '', '&', PHP_QUERY_RFC3986),
        ]);

        $data = $response->toArray(false);

        if ($response->getStatusCode() >= 400) {
            $message = $data['error_description'] ?? $data['error'] ?? 'Réponse invalide du serveur OAuth France Travail.';

            throw new \RuntimeException(sprintf('Authentification France Travail impossible (%d): %s', $response->getStatusCode(), $message));
        }

        if (!isset($data['access_token'])) {
            throw new \RuntimeException('Token France Travail introuvable.');
        }

        $this->accessToken = $data['access_token'];

        return $this->accessToken;
    }

    public function searchOffers(array $query = []): array
    {
        $response = $this->requestOffers($query);

        return $response->toArray();
    }

    public function countOffers(array $query = []): int
    {
        $response = $this->requestOffers($query + ['range' => '0-0']);
        $contentRange = $response->getHeaders(false)['content-range'][0] ?? null;

        if (is_string($contentRange) && preg_match('/\boffres\s+\d+-\d+\/(\d+)\b/', $contentRange, $matches) === 1) {
            return (int) $matches[1];
        }

        $data = $response->toArray(false);

        return count($data['resultats'] ?? []);
    }

    private function requestOffers(array $query): ResponseInterface
    {
        $token = $this->getAccessToken();

        return $this->httpClient->request(
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
    }
}
