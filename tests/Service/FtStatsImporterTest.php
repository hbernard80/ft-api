<?php

namespace App\Tests\Service;

use App\Entity\FtStats;
use App\Repository\FtStatsRepository;
use App\Service\FranceTravailClientService;
use App\Service\FtStatsImporter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FtStatsImporterTest extends KernelTestCase
{
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
            [
                'commune' => '80021',
                'rayon' => 10,
                'minCreationDate' => '2026-05-14T12:00:00+00:00',
                'maxCreationDate' => '2026-05-15T12:00:00+00:00',
            ],
            ['commune' => '80021', 'rayon' => 10, 'typeContrat' => 'CDI'],
            ['commune' => '80021', 'rayon' => 10, 'origineOffre' => '1'],
            ['commune' => '80021', 'rayon' => 10, 'origineOffre' => '1', 'typeContrat' => 'CDI'],
            [
                'commune' => '80021',
                'rayon' => 10,
                'origineOffre' => '1',
                'minCreationDate' => '2026-05-14T12:00:00+00:00',
                'maxCreationDate' => '2026-05-15T12:00:00+00:00',
            ],
        ], $capturedQueries);

        self::assertNotNull($repository->findOneBy(['date' => new \DateTime('2026-05-15')]));
    }
}
