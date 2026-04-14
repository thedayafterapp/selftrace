<?php

namespace App\Repository;

use App\Entity\Reflection;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reflection>
 */
class ReflectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reflection::class);
    }

    /** @return Reflection[] */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTodayByUser(User $user): ?Reflection
    {
        $today = new \DateTime();
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.date = :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns a map of date string => true for the last N weeks
     * @return array<string, bool>
     */
    public function getWritingDatesForHeatmap(User $user, int $weeks = 12): array
    {
        $since = new \DateTime("-{$weeks} weeks");
        $results = $this->createQueryBuilder('r')
            ->select('r.date')
            ->andWhere('r.user = :user')
            ->andWhere('r.date >= :since')
            ->setParameter('user', $user)
            ->setParameter('since', $since->format('Y-m-d'))
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($results as $row) {
            $dateStr = $row['date'] instanceof \DateTimeInterface
                ? $row['date']->format('Y-m-d')
                : $row['date'];
            $map[$dateStr] = true;
        }
        return $map;
    }
}
