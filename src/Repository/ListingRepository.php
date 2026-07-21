<?php

namespace App\Repository;

use App\Entity\Listing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Listing>
 */
class ListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }

    public function findFiltered(?string $search, ?int $category): array {
        $qb = $this->createQueryBuilder('listing');
        $qb->where('listing.status = :status')->setParameter('status', 'open');

        if ($search) {
            $qb->andWhere('listing.title LIKE :search')->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $qb->andWhere('listing.category = :category')->setParameter('category', $category);
        }

        return $qb->orderBy('listing.createdAt', 'DESC')->getQuery()->getResult();
    }

//    /**
//     * @return Listing[] Returns an array of Listing objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Listing
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
