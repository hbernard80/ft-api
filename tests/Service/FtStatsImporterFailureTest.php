<?php

namespace App\Tests\Service;

use App\Repository\FtStatsRepository;
use App\Service\FranceTravailClientService;
use App\Service\FtStatsImporter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

final class FtStatsImporterFailureTest extends TestCase
{
    public function testApiFailureDuringImportPreventsPersistence(): void
    {
        $client = new FranceTravailClientService(
            new MockHttpClient([
                new MockResponse('{"access_token":"token"}'),
                new MockResponse('{"resultats":[{}]}', [
                    'http_code' => 206,
                    'response_headers' => ['Content-Range' => 'offres 0-0/120'],
                ]),
                new MockResponse('{"error":"server_error"}', ['http_code' => 500]),
            ]),
            'client-id',
            'client-secret',
            'https://auth.example.test/token',
            'https://api.example.test'
        );
        $repository = $this->createMock(FtStatsRepository::class);
        $repository->expects(self::once())->method('findOneBy')->willReturn(null);
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects(self::never())->method('persist');
        $manager->expects(self::never())->method('flush');

        $importer = new FtStatsImporter($client, $repository, $manager);

        $this->expectException(HttpExceptionInterface::class);

        $importer->importForDate(new \DateTimeImmutable('2026-11-02T12:00:00+00:00'));
    }
}
