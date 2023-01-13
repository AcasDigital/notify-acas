<?php

namespace App\Repository;

use App\Entity\SubmissionSearchIndexItemLastName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubmissionSearchIndexItemLastName>
 */
class SubmissionSearchIndexItemLastNameRepository extends ServiceEntityRepository implements SubmissionSearchIndexItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionSearchIndexItemLastName::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(SubmissionSearchIndexItemLastName $entity, bool $flush = true): void
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
    public function remove(SubmissionSearchIndexItemLastName $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return SubmissionSearchIndexItemLastName[] Returns an array of SubmissionSearchIndexItemLastName objects
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
    //  * @return SubmissionSearchIndexItem[] Returns an array of SubmissionSearchIndexItemLastName objects
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
    public function findOneBySomeField($value): ?SubmissionSearchIndexItemLastName
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
