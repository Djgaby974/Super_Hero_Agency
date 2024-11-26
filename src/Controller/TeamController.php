<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/team')]
final class TeamController extends AbstractController
{
    #[Route(name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des contraintes
            $error = $this->validateTeamConstraints($team);

            if ($error) {
                $this->addFlash('error', $error); // Affiche l'erreur correspondante
            } else {
                $team->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($team);
                $entityManager->flush();

                $this->addFlash('success', 'Équipe créée avec succès !');
                return $this->redirectToRoute('app_team_index');
            }
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des contraintes
            $error = $this->validateTeamConstraints($team);

            if ($error) {
                $this->addFlash('error', $error); // Affiche l'erreur correspondante
            } else {
                $entityManager->flush();

                $this->addFlash('success', 'Équipe modifiée avec succès !');
                return $this->redirectToRoute('app_team_index');
            }
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->request->get('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
            $this->addFlash('success', 'Équipe supprimée avec succès !');
        }

        return $this->redirectToRoute('app_team_index');
    }

    /**
     * Valide les contraintes spécifiques d'une équipe.
     *
     * @param Team $team
     * @return string|null Renvoie un message d'erreur ou null si tout est valide.
     */
    private function validateTeamConstraints(Team $team): ?string
    {
        // Vérification du leader
        if (!$team->getLeader()) {
            return 'Vous devez sélectionner un leader pour l\'équipe.';
        }
        if ($team->getLeader()->getEnergyLevel() <= 80) {
            return 'Le leader doit avoir un niveau d\'énergie supérieur à 80.';
        }

        // Vérification des membres
        $memberCount = count($team->getMembers());
        if ($memberCount < 2 || $memberCount > 5) {
            return 'Une équipe doit avoir entre 2 et 5 membres.';
        }

        return null; // Pas d'erreur
    }
}
