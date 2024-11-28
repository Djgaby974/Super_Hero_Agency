<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Form\MissionType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mission')]
final class MissionController extends AbstractController
{
    #[Route(name: 'app_mission_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        // Récupération du paramètre de recherche
        $search = $request->query->get('search', '');

        // Création de la requête pour les missions
        $queryBuilder = $entityManager->getRepository(Mission::class)->createQueryBuilder('m');

        if (!empty($search)) {
            $queryBuilder->where('m.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Pagination des résultats
        $missions = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), // Page actuelle
            10 // Nombre de résultats par page
        );

        // Mise à jour des statuts pour les missions affichées
        foreach ($missions as $mission) {
            $mission->updateStatus();
        }
        $entityManager->flush();

        return $this->render('mission/index.html.twig', [
            'missions' => $missions,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_mission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Validation des dates
            if ($mission->getEndAt() <= $mission->getStartAt()) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');
                return $this->render('mission/new.html.twig', [
                    'mission' => $mission,
                    'form' => $form->createView(),
                ]);
            }
    
            // Mise à jour automatique du statut
            $mission->updateStatus(); // Important : recalcul du statut ici avant la persistance
    
            // Sauvegarde
            $entityManager->persist($mission);
            $entityManager->flush();
    
            $this->addFlash('success', 'Mission créée avec succès.');
            return $this->redirectToRoute('app_mission_index');
        }
    
        return $this->render('mission/new.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_mission_show', methods: ['GET'])]
    public function show(Mission $mission, EntityManagerInterface $entityManager): Response
    {
        // Mise à jour automatique du statut
        $mission->updateStatus();
        $entityManager->flush();

        return $this->render('mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation des dates
            if ($mission->getEndAt() <= $mission->getStartAt()) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');
                return $this->render('mission/edit.html.twig', [
                    'mission' => $mission,
                    'form' => $form->createView(),
                ]);
            }

            // Mise à jour automatique du statut
            $mission->updateStatus();

            // Validation des contraintes supplémentaires
            if (
                !$this->validateTeamAvailability($entityManager, $mission, true) ||
                !$this->validateRequiredPowers($mission)
            ) {
                return $this->render('mission/edit.html.twig', [
                    'mission' => $mission,
                    'form' => $form->createView(),
                ]);
            }

            // Sauvegarde des modifications
            $entityManager->flush();

            $this->addFlash('success', 'Mission mise à jour avec succès.');
            return $this->redirectToRoute('app_mission_index');
        }

        return $this->render('mission/edit.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_mission_delete', methods: ['POST'])]
    public function delete(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $mission->getId(), $request->request->get('_token'))) {
            $entityManager->remove($mission);
            $entityManager->flush();
            $this->addFlash('success', 'Mission supprimée avec succès.');
        }

        return $this->redirectToRoute('app_mission_index');
    }

    /**
     * Valide si l'équipe assignée est disponible pour la période donnée.
     */
    private function validateTeamAvailability(EntityManagerInterface $entityManager, Mission $mission, bool $isEdit = false): bool
    {
        $assignedTeam = $mission->getAssignedTeam();
        if ($assignedTeam) {
            $queryBuilder = $entityManager->createQueryBuilder()
                ->select('m')
                ->from(Mission::class, 'm')
                ->where('m.assignedTeam = :team')
                ->andWhere(':startAt BETWEEN m.startAt AND m.endAt OR :endAt BETWEEN m.startAt AND m.endAt')
                ->setParameter('team', $assignedTeam)
                ->setParameter('startAt', $mission->getStartAt())
                ->setParameter('endAt', $mission->getEndAt());

            if ($isEdit) {
                $queryBuilder->andWhere('m.id != :currentMissionId')
                    ->setParameter('currentMissionId', $mission->getId());
            }

            $conflictingMissions = $queryBuilder->getQuery()->getResult();

            if (!empty($conflictingMissions)) {
                $this->addFlash('error', 'L\'équipe est déjà assignée à une autre mission durant cette période.');
                return false;
            }
        }
        return true;
    }

    /**
     * Valide si l'équipe assignée possède les pouvoirs requis.
     */
    private function validateRequiredPowers(Mission $mission): bool
    {
        $assignedTeam = $mission->getAssignedTeam();
        $requiredPowers = $mission->getRequiredPowers();

        if ($assignedTeam) {
            $teamPowers = [];
            foreach ($assignedTeam->getMembers() as $member) {
                foreach ($member->getPowers() as $power) {
                    $teamPowers[] = $power->getName();
                }
            }

            $missingPowers = [];
            foreach ($requiredPowers as $requiredPower) {
                if (!in_array($requiredPower->getName(), $teamPowers)) {
                    $missingPowers[] = $requiredPower->getName();
                }
            }

            if (!empty($missingPowers)) {
                $this->addFlash('error', 'L\'équipe n\'a pas les pouvoirs requis : ' . implode(', ', $missingPowers));
                return false;
            }
        }
        return true;
    }
}
