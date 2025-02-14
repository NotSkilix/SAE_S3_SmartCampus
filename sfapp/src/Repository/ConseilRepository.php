<?php

namespace App\Repository;

use App\Entity\Batiment;
use App\Entity\Conseil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conseil>
 */
class ConseilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conseil::class);
    }

    /**
     * @author Axel
     * @brief Récupère tous les conseils d'un bâtiment avec le nom de la salle
     * @param string $batiment le bâtiment dans lequel on souhaite avoir tout les conseils
     * @return array tableau contenant la réponse de la requête
     */
    public function getAllInBatimentWithSalle(string $batiment) : array
    {
        return $this->createQueryBuilder('conseil')
            ->select('conseil.type', 'conseil.description', 'conseil.text', 'conseil.valeur', 'salle.nom AS nomSalle')
            ->join('conseil.salle', 'salle') // Join la salle pour avoir le nom
            ->join('salle.etage' , 'etage') // Join l'étage pour ensuite avoir le bâtiment en paramètre
            ->join('etage.batiment' , 'batiment')
            ->where('batiment.nom = :batiment')
            ->setParameter('batiment', $batiment)
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Récupère tous les conseils en fonction de l'id de la salle
     * @param int $salle l'id de la salle
     * @return array la réponse
     */
    public function getAllBySalle(int $salle) : array
    {
        return $this->createQueryBuilder('conseil')
            ->select('conseil.type', 'conseil.description', 'conseil.text', 'conseil.valeur')
            ->where('conseil.salle = :salle')
            ->setParameter('salle', $salle)
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Récupère tous les conseils d'un bâtiment
     * @param Batiment $batiment l'entité bâtiment qu'on utilise comme filtre
     * @return array la réponse dans un tableau
     */
    public function getAllByBatiment(Batiment $batiment) : array
    {
        return $this->createQueryBuilder('conseil')
            ->join('conseil.salle' , 'salle')
            ->join('salle.etage' , 'etage')
            ->where('etage.batiment = :batiment')
            ->setParameter('batiment', $batiment)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Conseil[] Returns an array of Conseil objects
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

//    public function findOneBySomeField($value): ?Conseil
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
