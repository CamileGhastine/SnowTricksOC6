<?php

namespace App\Repository;


use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function findAllWithPoster()
    {
        return $this->createQueryBuilder('t')
            ->addSelect('i')
            ->leftJoin('t.images', 'i')
            ->where('i.poster = 1')
            ->orWhere('i.poster IS NULL')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCategoryWithPoster($id)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('i')
            ->innerJoin('t.categories', 'c')
            ->leftJoin('t.images', 'i')
            ->where('c.id = :id')
            ->andWhere('i.poster = 1 OR i.poster IS NULL')
            ->setParameter('id', $id)
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findTrickWithCommentsAndCategories($id)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('ca')
            ->addSelect('co')
            ->addSelect('u')
            ->leftJoin('t.categories', 'ca')
            ->leftJoin('t.comments', 'co')
            ->leftJoin('co.user', 'u')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Trick[] Returns an array of Trick objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
