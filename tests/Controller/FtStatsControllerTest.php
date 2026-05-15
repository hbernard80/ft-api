<?php

namespace App\Tests\Controller;

use App\Entity\FtStats;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FtStatsControllerTest extends WebTestCase
{
    public function testIndexDisplaysExistingStats(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $manager */
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = $manager->getRepository(FtStats::class);

        foreach ($repository->findAll() as $object) {
            $manager->remove($object);
        }

        $stat = (new FtStats())
            ->setDate(new \DateTime('2026-05-14'))
            ->setJobs(120)
            ->setJobsFt(80)
            ->setJobs1j(12)
            ->setJobsFt1j(7)
            ->setJobsCdi(40)
            ->setJobsFtCdi(25);

        $manager->persist($stat);
        $manager->flush();

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('FtStats index');
        self::assertSelectorTextContains('table', '2026-05-14');
        self::assertSelectorTextContains('table', '120');
        self::assertSelectorTextContains('table', '25');
    }
}
