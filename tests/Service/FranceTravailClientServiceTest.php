<?php

namespace App\Tests\Service;

use App\Service\FranceTravailClientService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FranceTravailClientServiceTest extends TestCase
{
    public function testCountOffersReadsTotalFromContentRangeAndUsesOneResultRange(): void
    {
        $requests = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];

            if ($method === 'POST') {
                return new MockResponse('{"access_token":"token"}', ['http_code' => 200]);
            }

            return new MockResponse('{"resultats":[{}]}', [
                'http_code' => 206,
                'response_headers' => ['Content-Range' => 'offres 0-0/123'],
            ]);
        });

        $service = new FranceTravailClientService(
            $httpClient,
            'client-id',
            'client-secret',
            'https://auth.example.test/token',
            'https://api.example.test'
        );

        self::assertSame(123, $service->countOffers(['commune' => '80021']));
        self::assertCount(2, $requests);
        self::assertSame('POST', $requests[0][0]);
        self::assertSame('grant_type=client_credentials&client_id=client-id&client_secret=client-secret', $requests[0][2]['body']);
        self::assertSame('GET', $requests[1][0]);
        self::assertSame('/partenaire/offresdemploi/v2/offres/search', parse_url($requests[1][1], PHP_URL_PATH));
        self::assertSame(['commune' => '80021', 'range' => '0-0'], $requests[1][2]['query']);
        self::assertSame('Bearer token', $requests[1][2]['headers']['Authorization']);
    }

    public function testGetAccessTokenSendsConfiguredScope(): void
    {
        $requests = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];

            return new MockResponse('{"access_token":"token"}', ['http_code' => 200]);
        });

        $service = new FranceTravailClientService(
            $httpClient,
            'client-id',
            'client-secret',
            'https://auth.example.test/token',
            'https://api.example.test',
            'api_offresdemploiv2 o2dsoffre'
        );

        self::assertSame('token', $service->getAccessToken());
        self::assertSame('grant_type=client_credentials&client_id=client-id&client_secret=client-secret&scope=api_offresdemploiv2%20o2dsoffre', $requests[0][2]['body']);
    }

    public function testGetAccessTokenThrowsExplicitExceptionOnOauthError(): void
    {
        $httpClient = new MockHttpClient(new MockResponse(
            '{"error":"invalid_scope","error_description":"Le scope est invalide"}',
            ['http_code' => 400]
        ));

        $service = new FranceTravailClientService(
            $httpClient,
            'client-id',
            'client-secret',
            'https://auth.example.test/token',
            'https://api.example.test',
            'api_offresdemploiv2 o2dsoffre'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Authentification France Travail impossible (400): Le scope est invalide');

        $service->getAccessToken();
    }
}
