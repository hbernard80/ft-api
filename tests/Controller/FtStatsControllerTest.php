<?php

namespace App\Tests\Controller;

use App\Entity\FtStats;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FtStatsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<FtStats> */
    private EntityRepository $ftStatRepository;
    private string $path = '/ft/stats/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->ftStatRepository = $this->manager->getRepository(FtStats::class);

        foreach ($this->ftStatRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('FtStat index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'ft_stat[date]' => 'Testing',
            'ft_stat[jobs]' => 'Testing',
            'ft_stat[jobs_ft]' => 'Testing',
            'ft_stat[jobs_1j]' => 'Testing',
            'ft_stat[jobs_ft_1j]' => 'Testing',
            'ft_stat[jobs_cdi]' => 'Testing',
            'ft_stat[jobs_ft_cdi]' => 'Testing',
        ]);

        self::assertResponseRedirects('/ft/stats');

        self::assertSame(1, $this->ftStatRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new FtStats();
        $fixture->setDate('My Title');
        $fixture->setJobs('My Title');
        $fixture->setJobsFt('My Title');
        $fixture->setJobs1j('My Title');
        $fixture->setJobsFt1j('My Title');
        $fixture->setJobsCdi('My Title');
        $fixture->setJobsFtCdi('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('FtStat');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new FtStats();
        $fixture->setDate('Value');
        $fixture->setJobs('Value');
        $fixture->setJobsFt('Value');
        $fixture->setJobs1j('Value');
        $fixture->setJobsFt1j('Value');
        $fixture->setJobsCdi('Value');
        $fixture->setJobsFtCdi('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'ft_stat[date]' => 'Something New',
            'ft_stat[jobs]' => 'Something New',
            'ft_stat[jobs_ft]' => 'Something New',
            'ft_stat[jobs_1j]' => 'Something New',
            'ft_stat[jobs_ft_1j]' => 'Something New',
            'ft_stat[jobs_cdi]' => 'Something New',
            'ft_stat[jobs_ft_cdi]' => 'Something New',
        ]);

        self::assertResponseRedirects('/ft/stats');

        $fixture = $this->ftStatRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDate());
        self::assertSame('Something New', $fixture[0]->getJobs());
        self::assertSame('Something New', $fixture[0]->getJobsFt());
        self::assertSame('Something New', $fixture[0]->getJobs1j());
        self::assertSame('Something New', $fixture[0]->getJobsFt1j());
        self::assertSame('Something New', $fixture[0]->getJobsCdi());
        self::assertSame('Something New', $fixture[0]->getJobsFtCdi());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new FtStats();
        $fixture->setDate('Value');
        $fixture->setJobs('Value');
        $fixture->setJobsFt('Value');
        $fixture->setJobs1j('Value');
        $fixture->setJobsFt1j('Value');
        $fixture->setJobsCdi('Value');
        $fixture->setJobsFtCdi('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/ft/stats');
        self::assertSame(0, $this->ftStatRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
