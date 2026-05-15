<?php

namespace App\Form;

use App\Entity\FtStats;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FtStatsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('jobs')
            ->add('jobs_ft')
            ->add('jobs_1j')
            ->add('jobs_ft_1j')
            ->add('jobs_cdi')
            ->add('jobs_ft_cdi')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FtStats::class,
        ]);
    }
}
