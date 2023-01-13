<?php

namespace App\Repository;

use App\Entity\JobHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobHistory>
 */
class JobHistoryRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobHistory::class);
    }

    public function getQueryBuilder(?string $term = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d');
        if ($term) {
            $qb->andWhere('d.type = :term')
                ->setParameter('term', $term)
            ;
        }

        return $qb
            ->orderBy('d.started', 'DESC')
        ;
    }

    public function getLastSuccessfulJobByType(string $class): ?JobHistory
    {
        return $this->findOneBy(['type' => $class, 'status' => JobHistory::STATUS_SUCCESS], ['started' => 'DESC']);
    }
}
