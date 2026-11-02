<?php

namespace App\Tests\Service;

use App\Entity\FtStats;
use App\Repository\FtStatsRepository;
use App\Service\FranceTravailClientService;
use App\Service\FtStatsImporter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FtStatsImporterTest extends KernelTestCase
{
    public function testRepeatedImportUpdatesTheSameDailyStats(): void
    {
        self::bootKernel();
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = $manager->getRepository(FtStats::class);
        $date = new \DateTimeImmutable('2026-05-16T12:00:00+00:00');
        $manager->getConnection()->delete('ft_stats', ['date' => '2026-05-16']);

        $client = $this->createMock(FranceTravailClientService::class);
        $client->expects(self::exactly(12))->method('countOffers')
            ->willReturnOnConsecutiveCalls(...[...array_fill(0, 6, 120), ...array_fill(0, 6, 121)]);
        $importer = new FtStatsImporter($client, $repository, $manager);

        $id = $importer->importForDate($date)->getId();
        $manager->clear();
        $updatedStat = $importer->importForDate($date);

        self::assertSame($id, $updatedStat->getId());
        $manager->clear();
        self::assertSame(1, $repository->count(['date' => new \DateTime('2026-05-16')]));
        self::assertSame(121, $repository->find($id)->getJobs());
    }

    public function testDatabaseRejectsDuplicateDates(): void
    {
        self::bootKernel();
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $row = [
            'date' => '2026-05-17',
            'jobs' => 120,
            'jobs_ft' => 80,
            'jobs_1j' => 12,
            'jobs_ft_1j' => 7,
            'jobs_cdi' => 40,
            'jobs_ft_cdi' => 25,
        ];
        $connection->delete('ft_stats', ['date' => $row['date']]);
        $connection->insert('ft_stats', $row);

        $this->expectException(UniqueConstraintViolationException::class);

        $connection->insert('ft_stats', $row);
    }

    public function testImportForDateCountsExpectedAmiensSearchesAndPersistsStats(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $manager */
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var FtStatsRepository $repository */
        $repository = $manager->getRepository(FtStats::class);

        foreach ($repository->findAll() as $object) {
            $manager->remove($object);
        }
        $manager->flush();

        $capturedQueries = [];
        $counts = [120, 12, 40, 80, 25, 7];

        /** @var FranceTravailClientService&MockObject $franceTravailClient */
        $franceTravailClient = $this->createMock(FranceTravailClientService::class);
        $franceTravailClient->expects(self::exactly(6))
            ->method('countOffers')
            ->willReturnCallback(function (array $query) use (&$capturedQueries, &$counts): int {
                $capturedQueries[] = $query;

                return array_shift($counts);
            });

        $importer = new FtStatsImporter($franceTravailClient, $repository, $manager);
        $stat = $importer->importForDate(new \DateTimeImmutable('2026-05-15T12:00:00+00:00'));

        self::assertSame(120, $stat->getJobs());
        self::assertSame(12, $stat->getJobs1j());
        self::assertSame(40, $stat->getJobsCdi());
        self::assertSame(80, $stat->getJobsFt());
        self::assertSame(25, $stat->getJobsFtCdi());
        self::assertSame(7, $stat->getJobsFt1j());

        self::assertSame([
            ['commune' => '80021', 'rayon' => 10],
            ['commune' => '80021', 'rayon' => 10, 'publieeDepuis' => 1],
            ['commune' => '80021', 'rayon' => 10, 'typeContrat' => 'CDI'],
            ['commune' => '80021', 'rayon' => 10, 'origineOffre' => '1'],
            ['commune' => '80021', 'rayon' => 10, 'origineOffre' => '1', 'typeContrat' => 'CDI'],
            ['commune' => '80021', 'rayon' => 10, 'origineOffre' => '1', 'publieeDepuis' => 1],
        ], $capturedQueries);

        self::assertNotNull($repository->findOneBy(['date' => new \DateTime('2026-05-15')]));
    }
}
