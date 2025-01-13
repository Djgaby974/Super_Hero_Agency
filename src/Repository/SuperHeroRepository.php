<?php

namespace App\Repository;

use App\Entity\SuperHero;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuperHero>
 */
class SuperHeroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperHero::class);
    }

    public function findByFilters(?string $availability = null, ?string $energyLevel = null): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($availability !== null && $availability !== '') {
            $queryBuilder->andWhere('s.isAvailable = :isAvailable')
                        ->setParameter('isAvailable', $availability);
        }

        if ($energyLevel !== null && $energyLevel !== '') {
            $queryBuilder->andWhere('s.energyLevel >= :energyLevel')
                        ->setParameter('energyLevel', $energyLevel);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
