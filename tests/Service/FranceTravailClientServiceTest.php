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
        self::assertSame('GET', $requests[1][0]);
        self::assertSame('/partenaire/offresdemploi/v2/offres/search', parse_url($requests[1][1], PHP_URL_PATH));
        self::assertSame(['commune' => '80021', 'range' => '0-0'], $requests[1][2]['query']);
        self::assertSame('Bearer token', $requests[1][2]['headers']['Authorization']);
    }
}
