<?php

namespace App\Repository;

use App\Entity\Plan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plan>
 */
class PlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plan::class);
    }

     /**
     * @author Victor
     * @brief Récupère les plans d'une salle
     * @param int $idSalle : l'id de la salle
     * @return array : les plans correspondants
     */
    public function findBySalle($idSalle): array
    {
        return $this->createQueryBuilder('p')
            ->select('sa.nom AS nom', 'sa.etat AS etat')
            ->join('p.SA','sa')
            ->where('p.salle = :idSalle')
            ->andWhere('p.date_desassociation IS NULL')
            ->setParameter('idSalle', $idSalle)
            ->getQuery()
            ->getResult()
       ;
    }

     /**
     * @author Victor
     * @brief Récupère un plan en fonction de l'id du sa, seulement les plans
     *          actifs (dont le sa est toujours associé)
     * @param int $idSA : l'id du sa
     * @return Plan : le plan correspondant
     */
    public function findBySA($idSA): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->where('p.SA = :id')
            ->andWhere('p.date_desassociation IS NULL')
            ->setParameter('id', $idSA)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @author Victor
     * @brief Récupère tous les plans du batiments
     * @param string $batimentNom : nom du batiment
     * @return array : les plan correspondants
     */
    public function findAllByBatiment($batimentNom): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.salle','s')
            ->join('p.SA','sa')
            ->join('s.etage','e')
            ->join('e.batiment','b')
            ->where('b.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->orderby('p.date_association')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @author Victor
     * @brief Récupère tous les plans en fonction du nom recherché
     * @param string $nom : nom recherché
     * @param string $batimentNom : nom du batiment
     * @return array : les plan correspondants
     */
    public function findBySAOrSalleName(string $nom, string $batimentNom)
    {
        return $this->createQueryBuilder('p')
            ->join('p.salle','s')
            ->join('p.SA','sa')
            ->join('s.etage','e')
            ->join('e.batiment','b')
            ->where('b.nom = :batimentNom')
            ->andWhere('s.nom LIKE :nom OR sa.nom LIKE :nom')
            ->setParameter('nom','%' . $nom . '%')
            ->setParameter('batimentNom', $batimentNom)
            ->orderby('p.date_association')
            ->getQuery()
            ->getResult()
        ;
    }
}
