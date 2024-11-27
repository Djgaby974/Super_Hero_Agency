<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Form\MissionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mission')]
final class MissionController extends AbstractController
{
    #[Route(name: 'app_mission_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les missions triées par ID
        $missions = $entityManager->getRepository(Mission::class)->findBy([], ['id' => 'ASC']);

        return $this->render('mission/index.html.twig', [
            'missions' => $missions,
        ]);
    }

    #[Route('/new', name: 'app_mission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des dates
            if ($mission->getEndAt() <= $mission->getStartAt()) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');
                return $this->render('mission/new.html.twig', [
                    'mission' => $mission,
                    'form' => $form->createView(),
                ]);
            }

            // Validation du statut "en cours"
            if ($mission->getStatus() === 'in_progress') {
                $now = new \DateTime();
                if (!($mission->getStartAt() <= $now && $mission->getEndAt() >= $now)) {
                    $this->addFlash('error', 'Les dates ne correspondent pas au statut "en cours".');
                    return $this->render('mission/new.html.twig', [
                        'mission' => $mission,
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Validation de la disponibilité de l'équipe
            $assignedTeam = $mission->getAssignedTeam();
            if ($assignedTeam) {
                $conflictingMissions = $entityManager->createQueryBuilder()
                    ->select('m')
                    ->from(Mission::class, 'm')
                    ->where('m.assignedTeam = :team')
                    ->andWhere(':startAt BETWEEN m.startAt AND m.endAt OR :endAt BETWEEN m.startAt AND m.endAt')
                    ->setParameter('team', $assignedTeam)
                    ->setParameter('startAt', $mission->getStartAt())
                    ->setParameter('endAt', $mission->getEndAt())
                    ->getQuery()
                    ->getResult();

                if (!empty($conflictingMissions)) {
                    $this->addFlash('error', 'L\'équipe est déjà assignée à une autre mission durant cette période.');
                    return $this->render('mission/new.html.twig', [
                        'mission' => $mission,
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Validation des pouvoirs requis
            $requiredPowers = $mission->getRequiredPowers();
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
                return $this->render('mission/new.html.twig', [
                    'mission' => $mission,
                    'form' => $form->createView(),
                ]);
            }

            // Si tout est valide, sauvegarder la mission
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
    public function show(Mission $mission): Response
    {
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

            // Validation du statut "en cours"
            if ($mission->getStatus() === 'in_progress') {
                $now = new \DateTime();
                if (!($mission->getStartAt() <= $now && $mission->getEndAt() >= $now)) {
                    $this->addFlash('error', 'Les dates ne correspondent pas au statut "en cours".');
                    return $this->render('mission/edit.html.twig', [
                        'mission' => $mission,
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Validation de la disponibilité de l'équipe
            $assignedTeam = $mission->getAssignedTeam();
            if ($assignedTeam) {
                $conflictingMissions = $entityManager->createQueryBuilder()
                    ->select('m')
                    ->from(Mission::class, 'm')
                    ->where('m.assignedTeam = :team')
                    ->andWhere(':startAt BETWEEN m.startAt AND m.endAt OR :endAt BETWEEN m.startAt AND m.endAt')
                    ->andWhere('m.id != :currentMissionId')
                    ->setParameter('team', $assignedTeam)
                    ->setParameter('startAt', $mission->getStartAt())
                    ->setParameter('endAt', $mission->getEndAt())
                    ->setParameter('currentMissionId', $mission->getId())
                    ->getQuery()
                    ->getResult();

                if (!empty($conflictingMissions)) {
                    $this->addFlash('error', 'L\'équipe est déjà assignée à une autre mission durant cette période.');
                    return $this->render('mission/edit.html.twig', [
                        'mission' => $mission,
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Validation des pouvoirs requis
            $requiredPowers = $mission->getRequiredPowers();
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
                return $this->render('mission/edit.html.twig', [
                    'mission' => $mission,
                    'form' => $form->createView(),
                ]);
            }

            // Sauvegarder les modifications
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
}
