<?php

namespace App\Command;

use App\Service\FtStatsImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ft-stats:import',
    description: 'Importe les statistiques quotidiennes des offres France Travail autour d\'Amiens.',
)]
class ImportFtStatsCommand extends Command
{
    public function __construct(private readonly FtStatsImporter $ftStatsImporter)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $stat = $this->ftStatsImporter->importForDate();

        $io->success(sprintf(
            'Statistiques France Travail importées pour le %s : %d offres, %d offres < 1 jour, %d CDI, %d France Travail, %d France Travail CDI, %d France Travail < 1 jour.',
            $stat->getDate()?->format('Y-m-d') ?? 'date inconnue',
            $stat->getJobs(),
            $stat->getJobs1j(),
            $stat->getJobsCdi(),
            $stat->getJobsFt(),
            $stat->getJobsFtCdi(),
            $stat->getJobsFt1j(),
        ));

        return Command::SUCCESS;
    }
}
