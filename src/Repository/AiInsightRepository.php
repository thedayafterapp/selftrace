<?php

namespace App\Repository;

use App\Entity\AiInsight;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiInsight>
 */
class AiInsightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiInsight::class);
    }

    /** @return AiInsight[] */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.generatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestByUserAndType(User $user, string $type): ?AiInsight
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('a.generatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
