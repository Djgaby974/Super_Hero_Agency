<?php

namespace App\Controller;

use App\Repository\MissionRepository;
use App\Repository\SuperHeroRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/statistic')]
class StatisticController extends AbstractController
{
    #[Route('/', name: 'app_statistic_index', methods: ['GET'])]
    public function index(
        MissionRepository $missionRepository,
        SuperHeroRepository $superHeroRepository,
        TeamRepository $teamRepository
    ): Response {
        $missionsInProgress = $missionRepository->findBy(['status' => 'in_progress']);
        $availableHeroes = $superHeroRepository->findBy(['isAvailable' => true]);
        $unavailableHeroes = $superHeroRepository->findBy(['isAvailable' => false]);
        $teams = $teamRepository->findAll();

        $teamStats = [];
        foreach ($teams as $team) {
            $missions = $team->getMissions();
            $totalMissions = count($missions);
            $successfulMissions = count(array_filter($missions->toArray(), function ($mission) {
                return $mission->getStatus() === 'success';
            }));

            $teamStats[] = [
                'team' => $team->getName(),
                'total' => $totalMissions,
                'success' => $successfulMissions,
            ];
        }

        return $this->render('statistic/index.html.twig', [
            'missionsInProgress' => $missionsInProgress,
            'availableHeroes' => $availableHeroes,
            'unavailableHeroes' => $unavailableHeroes,
            'teamStats' => $teamStats,
        ]);
    }
}
