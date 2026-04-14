<?php

namespace App\Repository;

use App\Entity\DailyPrompt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailyPrompt>
 */
class DailyPromptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyPrompt::class);
    }

    /** @return DailyPrompt[] */
    public function findAllOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
