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
        // Missions en cours
        $missionsInProgress = $missionRepository->findBy(['status' => 'in_progress']);

        // Héros disponibles et indisponibles
        $availableHeroes = $superHeroRepository->findBy(['isAvailable' => true]);
        $unavailableHeroes = $superHeroRepository->findBy(['isAvailable' => false]);

        // Statistiques des équipes
        $teams = $teamRepository->findAll();
        $teamStats = [];
        foreach ($teams as $team) {
            $missions = $team->getMissions();
            $totalMissions = count($missions);
            $successfulMissions = count(array_filter($missions->toArray(), function ($mission) {
                return $mission->isSuccessful();
            }));
            $successRate = $totalMissions > 0 ? round(($successfulMissions / $totalMissions) * 100, 2) : 0;

            $teamStats[] = [
                'team' => $team->getName(),
                'totalMissions' => $totalMissions,
                'successfulMissions' => $successfulMissions,
                'successRate' => $successRate,
            ];
        }

        // Statistiques pour Chart.js
        $chartData = [
            'labels' => array_map(fn($stat) => $stat['team'], $teamStats),
            'datasets' => [
                [
                    'label' => 'Taux de réussite (%)',
                    'data' => array_map(fn($stat) => $stat['successRate'], $teamStats),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];

        return $this->render('statistic/index.html.twig', [
            'missionsInProgress' => $missionsInProgress,
            'availableHeroes' => $availableHeroes,
            'unavailableHeroes' => $unavailableHeroes,
            'teamStats' => $teamStats,
            'chartData' => json_encode($chartData),
        ]);
    }
}
