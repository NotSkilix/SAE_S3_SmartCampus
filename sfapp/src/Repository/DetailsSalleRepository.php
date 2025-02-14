<?php

namespace App\Repository;

use App\Entity\DetailsSalle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetailsSalle>
 */
class DetailsSalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailsSalle::class);
    }

    public function findAllByIdSalle(int $idSalle): array
    {
        return $this->createQueryBuilder('details')
            ->select('details.superficie, details.radiateur, details.fenetre, details.exposition, details.porte, details.frequentation, etage.nomComplet AS nomEtage')
            ->leftJoin('details.salle', 'salle')
            ->leftJoin('salle.etage', 'etage')
            ->where('details.salle = :idSalle')
            ->setParameter('idSalle', $idSalle)
            ->getQuery()
            ->getResult();
        //->select('details.superficie, details.radiateur, details.fenetre, details.exposition, details.porte, details.frequentation')
    }

    //    /**
    //     * @return DetailsSalle[] Returns an array of DetailsSalle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DetailsSalle
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
