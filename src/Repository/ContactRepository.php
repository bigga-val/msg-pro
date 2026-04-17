<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }


    public function findContactsByUser($value): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.id, c.telephone, c.nom, c.postnom, c.adresse, c.fonction')
            ->where('c.user = :user')
            ->setParameter('user', $value)
            ->getQuery()
            ->getResult();
    }

    public function findContacts(bool $all = false): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.telephone, c.nom, c.postnom, c.adresse, c.fonction')
            ->getQuery()
            ->getResult();
    }




    //    /**
    //     * @return Contact[] Returns an array of Contact objects
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

    //    public function findOneBySomeField($value): ?Contact
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
