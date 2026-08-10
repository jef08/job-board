<?php

namespace App\Repository;

use App\Entity\Freelancer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;

/**
 * @extends ServiceEntityRepository<Freelancer>
 */
class FreelancerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Freelancer::class);
    }

    public function findFiltered(?Category $category): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('freelancer');

        if ($category) {
            $qb->andWhere('freelancer.category = :category')
            ->setParameter('category', $category);
        }

        return $qb->orderBy('freelancer.id', 'DESC');
    }

//    /**
//     * @return Freelancer[] Returns an array of Freelancer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Freelancer
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
