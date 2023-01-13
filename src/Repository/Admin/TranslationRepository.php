<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Translation;
use App\Repository\PaginationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Translation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translation[]    findAll()
 * @method Translation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Translation>
 */
class TranslationRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    /**
     * @return array<string, Translation>
     */
    public function findAllWithKey(): array
    {
        return $this->createQueryBuilder('t', 't.key')
            ->getQuery()
            ->getResult();
    }

    public function getQueryBuilder(?string $term = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d');
        if ($term) {
            $qb->andWhere('d.key LIKE :term OR d.translation LIKE :term')
                ->setParameter('term', '%'.$term.'%')
            ;
        }

        return $qb
            ->orderBy('d.key', 'DESC')
        ;
    }

    public function getOneByLowerCasedKey(string $key): ?Translation
    {
        return $this->findOneByKey(strtolower($key));
    }
}
