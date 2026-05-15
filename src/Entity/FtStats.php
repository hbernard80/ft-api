<?php

namespace App\Entity;

use App\Repository\FtStatsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FtStatsRepository::class)]
class FtStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jobs = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jobs_ft = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jobs_1j = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jobs_ft_1j = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jobs_cdi = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jobs_ft_cdi = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getJobs(): ?int
    {
        return $this->jobs;
    }

    public function setJobs(int $jobs): static
    {
        $this->jobs = $jobs;

        return $this;
    }

    public function getJobsFt(): ?int
    {
        return $this->jobs_ft;
    }

    public function setJobsFt(int $jobs_ft): static
    {
        $this->jobs_ft = $jobs_ft;

        return $this;
    }

    public function getJobs1j(): ?int
    {
        return $this->jobs_1j;
    }

    public function setJobs1j(int $jobs_1j): static
    {
        $this->jobs_1j = $jobs_1j;

        return $this;
    }

    public function getJobsFt1j(): ?int
    {
        return $this->jobs_ft_1j;
    }

    public function setJobsFt1j(int $jobs_ft_1j): static
    {
        $this->jobs_ft_1j = $jobs_ft_1j;

        return $this;
    }

    public function getJobsCdi(): ?int
    {
        return $this->jobs_cdi;
    }

    public function setJobsCdi(int $jobs_cdi): static
    {
        $this->jobs_cdi = $jobs_cdi;

        return $this;
    }

    public function getJobsFtCdi(): ?int
    {
        return $this->jobs_ft_cdi;
    }

    public function setJobsFtCdi(int $jobs_ft_cdi): static
    {
        $this->jobs_ft_cdi = $jobs_ft_cdi;

        return $this;
    }
}
