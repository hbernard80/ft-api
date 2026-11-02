<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Garantit une seule ligne de statistiques France Travail par date.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM (SELECT date FROM ft_stats GROUP BY date HAVING COUNT(*) > 1) AS duplicate_dates') > 0,
            'Des statistiques existent en double pour une même date. Résoudre ces doublons avant de relancer la migration.',
        );

        $this->addSql('CREATE UNIQUE INDEX uniq_ft_stats_date ON ft_stats (date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_ft_stats_date ON ft_stats');
    }
}
