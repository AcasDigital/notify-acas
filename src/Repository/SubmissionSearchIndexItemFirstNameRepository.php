<?php

namespace App\Repository;

use App\Entity\SubmissionSearchIndexItem;
use App\Entity\SubmissionSearchIndexItemFirstName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubmissionSearchIndexItemFirstName>
 */
class SubmissionSearchIndexItemFirstNameRepository extends ServiceEntityRepository implements SubmissionSearchIndexItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionSearchIndexItemFirstName::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(SubmissionSearchIndexItemFirstName $entity, bool $flush = true): void
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
    public function remove(SubmissionSearchIndexItemFirstName $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return SubmissionSearchIndexItemFirstName[] Returns an array of SubmissionSearchIndexItemFirstName objects
     */
    public function getAllByTaskDetails(string $disputeFormId, string $type, string $section): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.type = :type')
            ->setParameter('type', $type)
            ->andWhere('i.section = :section')
            ->setParameter('section', $section)
            ->leftJoin('i.employmentDispute', 'd')
            ->andWhere('d.id = :disputeFormId')
            ->setParameter('disputeFormId', $disputeFormId)
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return SubmissionSearchIndexItem[] Returns an array of SubmissionSearchIndexItemFirstName objects
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
    public function findOneBySomeField($value): ?SubmissionSearchIndexItemFirstName
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
