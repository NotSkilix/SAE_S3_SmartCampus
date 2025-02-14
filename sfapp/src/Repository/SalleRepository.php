<?php

namespace App\Repository;

use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salle>
 */
class SalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

     /**
     * @author Victor
     * @brief Permet de trouver si une salle existe avec le nom donné
     *          et différente de l'id donné
     * @param string $nom : nom de la salle
     * @param int $id : id de la salle
     * @param string $batiment : nom du batiment
     * @return Salle : une salle correspondante, peut être null
     */
    public function findByName(string $batimentNom, string $nom, int $id) : ?Salle
    {
        return $this->createQueryBuilder('i')
            ->join('i.etage','e')
            ->join('e.batiment','b')
            ->where('i.nom = :nom')
            ->andWhere('i.id != :id')
            ->andWhere('b.nom = :batimentNom')
            ->setParameter('nom', $nom)
            ->setParameter('id', $id)
            ->setParameter('batimentNom', $batimentNom)
            ->getQuery()
            ->getOneOrNullResult();
    }

     /**
     * @author ...
     * @brief Récupère le nom de toutes les salles
     * @return array : le nom des salles
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('salles')
            ->orderby('salles.nom')
            ->getQuery()
            ->getResult();
    }

    // récupérer le nom et l'id des salles et l'id du SA à partir de leur étage
    // utilisé pour afficher les information des salles

     /**
     * @author ...
     * @brief Récupère les nom et l'id des salles, l'id du sa ? et le nombre
     *          de SA en fonction d'un étage.
      *         qu'une salle a
     * @param int $etage : l'id d'un étage
     * @return array : les salles correspondantes
     */
    public function findByEtage(int $etageId): array
    {
        return $this->createQueryBuilder('salles')
            ->select('salles.nom, salles.id, sa.id AS sa_id, SUM(CASE WHEN plan.date_association IS NOT NULL AND plan.date_desassociation IS NULL THEN 1 ELSE 0 END) AS sa_count')
            ->leftJoin('salles.plans', 'plan')
            ->leftJoin('plan.SA', 'sa')
            ->join('salles.etage', 'e')
            ->where('e.id = :etageId')
            ->groupBy('salles.id') // Ajout du regroupement
            ->setParameter('etageId', $etageId)
            ->getQuery()
            ->getResult();
    }

     /**
     * @author ...
     * @brief Récupère une salle en fonction de l'id de son sa
     * @param int $saId : l'id d'un sa
     * @return array : la salle correspondante
     */
    public function findBySystemAcquisitionId(int $saId): array
    {
        return $this->createQueryBuilder('salle')
            ->innerJoin('salle.plans', 'plan')
            ->innerJoin('plan.SA', 'sa')
            ->where('sa.id = :saId')
            ->setParameter('saId', $saId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Récupère toutes les salles en fonction du string
     *        donné
     * @param string $nom : nom de la salle (si il existe)
     * @param string $batimentNom : nom du batiment
     * @return array : les salles correspondantes
     */
    public function findAllSalleByName(string $nom, string $batimentNom) : array
    {
        return $this->createQueryBuilder('salles')
            ->select('salles.nom, salles.id, sa.id AS sa_id, SUM(CASE WHEN plan.date_association IS NOT NULL AND plan.date_desassociation IS NULL THEN 1 ELSE 0 END) AS sa_count')
            ->leftJoin('salles.plans', 'plan')
            ->leftJoin('plan.SA', 'sa')
            ->join('salles.etage','e')
            ->join('e.batiment','b')
            ->groupBy('salles.id')
            ->where('salles.nom LIKE :nom')
            ->andWhere('b.nom = :batimentNom')
            ->setParameter('nom',  $nom . '%') // Ajout de "wildcards" pour chercher autour du nom
            ->setParameter('batimentNom' , $batimentNom)
            ->getQuery()
            ->getResult();
    }

    public function findAllInEtage(int $etage): array
    {
        return $this->createQueryBuilder('salles')
            ->where('salles.etage = :etage')
            ->setParameter('etage', $etage)
            ->getQuery()
            ->getResult();
    }
    /**
     * @author Victor
     * @brief Récupère toutes les salles qui ont un plan
     * @return array : les salles qui ont un plan
     * (utilisé pour l'affichage des plans)
     */
    public function findAllWithPlan($batimentNom) : array
    {
        return $this->createQueryBuilder('salles')
            ->select('DISTINCT salles.id, salles.nom, e.nomComplet AS etage')
            ->join('salles.plans','plan')
            ->join('salles.etage','e')
            ->join('e.batiment','b')
            ->where('plan IS NOT NULL')
            ->andWhere('plan.date_desassociation IS NULL')
            ->andWhere('b.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @author Victor
     * @brief Récupère toutes les salles en fonction de l'état donné
     * @param etat : état d'un sa souhaité
     * @return array : les salles dont un sa comporte l'état donné
     * (utilisé pour l'affichage des plans)
     */
    public function findByEtat($etat, $batimentNom) : array
    {
        return $this->createQueryBuilder('salles')
            ->select('DISTINCT salles.id, salles.nom, e.nomComplet AS etage')
            ->join('salles.plans', 'plan')
            ->join('plan.SA','sa')
            ->join('salles.etage','e')
            ->join('e.batiment','b')
            ->where('sa.etat = :etat')
            ->andWhere('plan.date_desassociation IS NULL')
            ->andWhere('b.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->setParameter('etat', $etat)
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Victor
     * @brief Récupère toutes les salles dont le nom ou un sa
     *          correspond à la variable entrée
     * @param string $nom : le nom entré
     * @return array : les salles correspondantes
     */
    public function findBySearch(string $nom, string $batimentNom) : array
    {
        return $this->createQueryBuilder('salles')
            ->select('DISTINCT salles.id, salles.nom, e.nomComplet AS etage')
            ->join('salles.plans', 'plan')
            ->join('plan.SA','sa')
            ->join('salles.etage','e')
            ->join('e.batiment','b')
            ->where('salles.nom LIKE :nom OR sa.nom LIKE :nom')
            ->andWhere('plan.date_desassociation IS NULL')
            ->andWhere('b.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->setParameter('nom','%' . $nom . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Victor
     * @brief Récupère toutes les salles d'un bâtiment
     * @param string $batimentNom Le nom du bâtiment
     * @return QueryBuilder Les salles correspondantes
     */
    public function findByBatiment(string $batimentNom): QueryBuilder
    {
        return $this->createQueryBuilder('salles')
            ->join('salles.etage','e')
            ->join('e.batiment','b')
            ->where('b.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->orderBy('salles.nom')
            ;
    }

    /**
     * @author Julien
     * @brief Récupère les identifiants des salles d'un bâtiment
     * @param string $batimentNom Le nom du bâtiment
     * @return array Liste des identifiants des salles correspondantes
     */
    public function findIdByBatiment(string $batimentNom): array
    {
        return $this->createQueryBuilder('salles')
            ->select('salles.id')
            ->join('salles.etage','e')
            ->join('e.batiment','b')
            ->where('b.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->orderBy('salles.nom')
            ->getQuery()
            ->getResult();
    }


    public function findSalleBySAId(int $saId): ?Salle
    {
        return $this->createQueryBuilder('salle')
            ->join('salle.plans','plan')
            ->where('plan.SA = :saId')
            ->setParameter('saId', $saId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @author Victor
     * @brief Retourne la salle dans lequel se trouve un capteur donné
     * @param int $idSensor : l'id d'un capteur de la salle
     * @return Salle : la salle recherchée
     */
    public function findBySensor(int $idSensor): ?Salle
    {
        return $this->createQueryBuilder('salle')
            ->select('salle.nom')
            ->join('salle.plans','plan')
            ->join('plan.SA','sa')
            ->join('sa.capteurs','ca')
            ->join('ca.id = :idSensor')
            ->where('plan.date_desassociation IS NULL')
            ->setParameter('idSensor', $idSensor)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @author Corentin, Julien
     * @brief Récupère l'ID d'une salle à partir de son nom et de son bâtiment.
     * @param string $nomSalle Nom de la salle recherchée
     * @param string $nomBatiment Nom du bâtiment où se trouve la salle
     * @return int|null L'ID de la salle ou null s'il n'existe pas
     */
    public function findIdByName(string $nomSalle, string $nomBatiment): ?int
    {
        $results = $this->createQueryBuilder('s')
            ->select('s.id')
            ->join('s.etage','e')
            ->join('e.batiment','b')
            ->where('s.nom = :nomSalle')
            ->andWhere('b.nom = :nomBatiment')
            ->setParameter('nomSalle', $nomSalle)
            ->setParameter('nomBatiment', $nomBatiment)
            ->getQuery()
            ->getScalarResult();

        // Return the first result if it exists, or null if no results
        return $results[0]['id'] ?? null;
    }

}
