<?php

namespace App\Service;

use App\Entity\FtStats;
use App\Repository\FtStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class FtStatsImporter
{
    private const AMIENS_COMMUNE_CODE = '80021';
    private const AMIENS_RADIUS_KM = 10;
    private const CDI_CONTRACT_CODE = 'CDI';
    private const FRANCE_TRAVAIL_ORIGIN_CODE = '1';

    public function __construct(
        private readonly FranceTravailClientService $franceTravailClient,
        private readonly FtStatsRepository $ftStatsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function importForDate(?\DateTimeImmutable $referenceDate = null): FtStats
    {
        $referenceDate ??= new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $date = \DateTime::createFromImmutable($referenceDate)->setTime(0, 0);
        $sinceYesterday = $referenceDate->sub(new \DateInterval('P1D'));

        $stat = $this->ftStatsRepository->findOneBy(['date' => $date]) ?? new FtStats();
        $stat->setDate($date)
            ->setJobs($this->countOffers())
            ->setJobs1j($this->countOffers($this->publishedSince($sinceYesterday, $referenceDate)))
            ->setJobsCdi($this->countOffers($this->cdiOnly()))
            ->setJobsFt($this->countOffers($this->franceTravailOnly()))
            ->setJobsFtCdi($this->countOffers($this->franceTravailOnly() + $this->cdiOnly()))
            ->setJobsFt1j($this->countOffers($this->franceTravailOnly() + $this->publishedSince($sinceYesterday, $referenceDate)));

        $this->entityManager->persist($stat);
        $this->entityManager->flush();

        return $stat;
    }

    /**
     * @param array<string, scalar> $filters
     */
    private function countOffers(array $filters = []): int
    {
        return $this->franceTravailClient->countOffers($this->baseQuery() + $filters);
    }

    /**
     * @return array<string, scalar>
     */
    private function baseQuery(): array
    {
        return [
            'commune' => self::AMIENS_COMMUNE_CODE,
            'rayon' => self::AMIENS_RADIUS_KM,
        ];
    }

    /**
     * @return array<string, scalar>
     */
    private function cdiOnly(): array
    {
        return [
            'typeContrat' => self::CDI_CONTRACT_CODE,
        ];
    }

    /**
     * @return array<string, scalar>
     */
    private function franceTravailOnly(): array
    {
        return [
            'origineOffre' => self::FRANCE_TRAVAIL_ORIGIN_CODE,
        ];
    }

    /**
     * @return array<string, scalar>
     */
    private function publishedSince(\DateTimeImmutable $minCreationDate, \DateTimeImmutable $maxCreationDate): array
    {
        return [
            'minCreationDate' => $minCreationDate->format(\DateTimeInterface::ATOM),
            'maxCreationDate' => $maxCreationDate->format(\DateTimeInterface::ATOM),
        ];
    }
}
