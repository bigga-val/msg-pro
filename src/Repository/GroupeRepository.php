<?php

namespace App\Repository;

use App\Entity\Groupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Groupe>
 */
class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupe::class);
    }


    public function findGroupesByUser($value): array
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'select g.id, g.designation,
             (select count(cg.id) from App\Entity\ContactGroupe cg where cg.groupe = g.id) as totalContact
             from App\Entity\Groupe g
             where g.user = :user
             order by g.id desc'
        );
        $query->setParameter('user', $value);
        return $query->getResult();
    }

    public function findGroupes(): array
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'select g.id, g.designation,
             (select count(cg.id) from App\Entity\ContactGroupe cg where cg.groupe = g.id) as totalContact
             from App\Entity\Groupe g
             order by g.id desc'
        );
        return $query->getResult();
    }

    public function findContactNotInGroupe($groupeID): array
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT c.id contactID, c.telephone, c.nom, c.postnom, c.fonction
                    FROM App\Entity\Contact c
                    where
                    c.id not in (SELECT c2.id from App\Entity\ContactGroupe cg, App\Entity\Contact c2 
                    where c2.id = cg.contact
                    and cg.groupe = :groupeID)
            '
        );
        //$query->setParameter('user', $userID);
        $query->setParameter('groupeID', $groupeID);
        //$query->setParameter('mydate', $mydate->format('Y-m-d'));
        return $query->getResult();
    }

    public function findContactNotInGroupeByUser($groupeID, $userID): array
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT c.id contactID, c.telephone, c.nom, c.postnom, c.fonction
                    FROM App\Entity\Contact c
                    where
                    c.id not in (SELECT c2.id from App\Entity\ContactGroupe cg, App\Entity\Contact c2 
                    where c2.id = cg.contact
                    and cg.groupe = :groupeID)
                    and c.user = :userID
            '
        );
        $query->setParameter('userID', $userID);
        $query->setParameter('groupeID', $groupeID);
        //$query->setParameter('mydate', $mydate->format('Y-m-d'));
        return $query->getResult();
    }


    //    /**
    //     * @return Groupe[] Returns an array of Groupe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Groupe
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
