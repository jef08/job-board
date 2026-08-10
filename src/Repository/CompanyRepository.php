<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findFeatured(int $limit = 5): array
    {
        return $this->createQueryBuilder('company')
            ->innerJoin('company.listings', 'listing')
            ->where('listing.status = :status')
            ->setParameter('status', 'open')
            ->groupBy('company.id')
            ->orderBy('company.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(?string $search): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('company');

        if ($search) {
            $qb->andWhere('company.industry LIKE :search')
            ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('company.id', 'DESC');
    }

//    /**
//     * @return Company[] Returns an array of Company objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Company
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
