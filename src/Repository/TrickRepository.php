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

    /**
     * @param null $maxResult
     * @param null $firstResult
     * @return int|mixed|string
     */
    public function findAllWithPoster($maxResult = null, $firstResult = null)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('i')
            ->leftJoin('t.images', 'i')
            ->where('i.poster = 1')
            ->orWhere('i.poster IS NULL')
            ->orderBy('t.updatedAt', 'DESC')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $id
     * @param null $maxResult
     * @param null $firstResult
     * @return int|mixed|string
     */
    public function findByCategoryWithPoster($id, $maxResult = null, $firstResult = null)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('i')
            ->innerJoin('t.categories', 'c')
            ->leftJoin('t.images', 'i')
            ->where('c.id = :id')
            ->andWhere('i.poster = 1 OR i.poster IS NULL')
            ->setParameter('id', $id)
            ->orderBy('t.updatedAt', 'DESC')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $id
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTrickWithCategoriesImagesVideosComments($id)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('ca')
            ->addSelect('i')
            ->addSelect('v')
            ->addSelect('co')
            ->leftJoin('t.categories', 'ca')
            ->leftJoin('t.images', 'i')
            ->leftJoin('t.videos', 'v')
            ->leftJoin('t.comments', 'co')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $id
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findWithPoster($id)
    {
        return $this->createQueryBuilder('t')
            ->addSelect('i')
            ->leftJoin('t.images', 'i')
            ->where('t.id = :id')
            ->andWhere('i.poster = 1 OR i.poster IS NULL')
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
