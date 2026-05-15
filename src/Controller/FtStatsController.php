<?php

namespace App\Controller;

//use App\Entity\FtStats;
//use App\Form\FtStatsType;
use App\Repository\FtStatsRepository;
//use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class FtStatsController extends AbstractController
{
    #[Route(name: 'app_ft_stats_index', methods: ['GET'])]
    public function index(FtStatsRepository $ftStatsRepository): Response
    {
        // +++ TODO : pagination +++    

        return $this->render('ft_stats/index.html.twig', [
            'ft_stats' => $ftStatsRepository->findAll(),
        ]);
    }

    /*
    #[Route('/ftstats/new', name: 'app_ft_stats_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ftStat = new FtStats();
        $form = $this->createForm(FtStatsType::class, $ftStat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ftStat);
            $entityManager->flush();

            return $this->redirectToRoute('app_ft_stats_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ft_stats/new.html.twig', [
            'ft_stat' => $ftStat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ft_stats_show', methods: ['GET'])]
    public function show(FtStats $ftStat): Response
    {
        return $this->render('ft_stats/show.html.twig', [
            'ft_stat' => $ftStat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ft_stats_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FtStats $ftStat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FtStatsType::class, $ftStat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ft_stats_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ft_stats/edit.html.twig', [
            'ft_stat' => $ftStat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ft_stats_delete', methods: ['POST'])]
    public function delete(Request $request, FtStats $ftStat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ftStat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ftStat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ft_stats_index', [], Response::HTTP_SEE_OTHER);
    }
    */
}
