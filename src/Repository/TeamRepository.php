<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }


    /**
     * Valide les contraintes spécifiques d'une équipe.
     *
     * @param Team $team
     * @return string|null Renvoie un message d'erreur ou null si tout est valide.
     */
    public function validateTeamConstraints(Team $team): ?string
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
