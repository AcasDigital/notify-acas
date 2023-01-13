<?php

namespace App\Repository;

use App\Entity\EmploymentDispute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmploymentDispute|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmploymentDispute|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmploymentDispute[]    findAll()
 * @method EmploymentDispute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<EmploymentDispute>
 */
class EmploymentDisputeRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmploymentDispute::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EmploymentDispute $entity, bool $flush = true): void
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
    public function remove(EmploymentDispute $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return EmploymentDispute[] Returns an array of EmploymentDispute objects
     */
    public function findBySRNandMemoWord(string $returnNumber, string $memorableWord)
    {
        $returnNumber = strtoupper($returnNumber);
        $memorableWord = strtoupper($memorableWord);

        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :srn')
            ->setParameter('srn', $returnNumber)
            ->andWhere('d.memorableWord = :memo')
            ->setParameter('memo', $memorableWord)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByReturnNumber(string $returnNumber): ?EmploymentDispute
    {
        $returnNumber = strtoupper($returnNumber);

        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :return')
            ->setParameter('return', $returnNumber)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return EmploymentDispute[] Returns an array of EmploymentDispute objects
     */
    public function findSubmittedBeforeDate(\DateTime $date)
    {
        return $this->createQueryBuilder('d')
            ->where('d.status = :status')
            ->andWhere('d.submissionDateTime < :date')
            ->setParameter('status', EmploymentDispute::STATUS_SUBMITTED)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return EmploymentDispute[] Returns an array of EmploymentDispute objects
     */
    public function findBySRNandEmail(string $returnNumber, string $email)
    {
        $returnNumber = strtoupper($returnNumber);
        $email = $email;

        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :srn')
            ->setParameter('srn', $returnNumber)
            ->andWhere('d.verificationContact = :email')
            ->setParameter('email', $email)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getQueryBuilder(?string $term = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d');

        return $qb
            ->orderBy('d.modified', 'DESC')
        ;
    }
}
