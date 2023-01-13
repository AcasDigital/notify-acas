<?php

namespace App\Repository;

use App\Entity\SubmissionSearchIndexItemRespondentName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubmissionSearchIndexItemRespondentName>
 */
class SubmissionSearchIndexItemRespondentNameRepository extends ServiceEntityRepository implements SubmissionSearchIndexItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionSearchIndexItemRespondentName::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(SubmissionSearchIndexItemRespondentName $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(SubmissionSearchIndexItemRespondentName $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return SubmissionSearchIndexItemRespondentName[] Returns an array of SubmissionSearchIndexItemRespondentName objects
     */
    public function getAllByTaskDetails(string $disputeFormId, string $type, string $section): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.type = :type')
            ->setParameter('type', $type)
            ->andWhere('i.section LIKE :section')
            ->setParameter('section', '%'.$section.'%')
            ->leftJoin('i.employmentDispute', 'd')
            ->andWhere('d.id = :disputeFormId')
            ->setParameter('disputeFormId', $disputeFormId)
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return SubmissionSearchIndexItem[] Returns an array of SubmissionSearchIndexItemRespondentName objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubmissionSearchIndexItemRespondentName
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
