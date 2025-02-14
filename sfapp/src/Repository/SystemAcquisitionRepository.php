<?php

namespace App\Repository;

use App\Entity\SystemAcquisition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SystemAcquisition>
 */
class SystemAcquisitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemAcquisition::class);
    }

    // Affiche tout les SA ainsi que s'ils ont une salle
    public function findAllSAAndSalle() : array
    {
        return $this->createQueryBuilder('sa')
            ->select('sa.nom', 'sa.id', 'salle.nom AS nomSalle')
            ->leftJoin('sa.plan', 'plan')
            ->innerJoin('plan.salle', 'salle')
            ->orderBy('salle.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Côme
     * @brief Affiche les SA attribué dans le bâtiment dans l'ordre
     *        ainsi que les autres SA non attribué
     * @param string $batiment = Nom du bâtiment
     * @return array = tout les SA
     */
    public function findAllByBatiment(string $batiment) : array
    {
        return $this->createQueryBuilder('systemAcquisition')
            ->select('systemAcquisition.nom', 'systemAcquisition.id', 'systemAcquisition.etat',
                'systemAcquisition.dateCreation', 'salle.nom AS nomSalle', 'plan.date_desassociation AS assossie')
            ->leftjoin('systemAcquisition.plan', 'plan')
            ->leftjoin('plan.salle','salle')
            ->leftjoin('salle.etage','etage')
            ->leftjoin('etage.batiment','batiment')
            ->andWhere('batiment.nom = :val or plan.id IS null')
            ->setParameter('val', $batiment)
            ->orderBy('salle.nom', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $nom, int $id = -1) : ?SystemAcquisition
    {
        return $this->createQueryBuilder('i')
            ->where('i.nom = :nom')
            ->andWhere('i.id != :id')
            ->setParameter('nom', $nom)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @author Julien
     * @brief Récupère les SA d'un bâtiment avec un état spécifique.
     * @param string $etat État recherché
     * @param string $batiment Nom du bâtiment
     * @return array Liste des SA correspondant à la recherche
     */
    public function findByEtat(string $etat, string $batiment): array
    {
        return $this->createQueryBuilder('i')
            ->select('i.nom', 'i.id', 'i.etat', 'i.dateCreation', 's.nom AS nomSalle', 'p.date_desassociation AS assossie')
            ->leftjoin('i.plan', 'p')
            ->leftjoin('p.salle','s')
            ->leftjoin('s.etage','e')
            ->leftjoin('e.batiment','b')
            ->where('i.etat = :etat')
            ->andWhere('b.nom = :bat or p.id IS null')
            ->setParameter('etat', $etat)
            ->setParameter('bat', $batiment)
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Julien
     * @brief Récupère les SA d'un bâtiment avec un état spécifique et un nom.
     * @param string $nom Nom recherché
     * @param string $etat État recherché
     * @param string $batiment Nom du bâtiment
     * @return array Liste des SA correspondant à la recherche
     */
    public function findByNameAndEtat(string $nom, string $etat, string $batiment): array
    {
        return $this->createQueryBuilder('i')
            ->select('i.nom', 'i.etat', 'i.dateCreation', 's.nom AS nomSalle', 'p.date_desassociation AS assossie')
            ->leftjoin('i.plan', 'p')
            ->leftjoin('p.salle','s')
            ->leftjoin('s.etage','e')
            ->leftjoin('e.batiment','b')
            ->where('i.nom LIKE :nom')
            ->andWhere('i.etat = :etat')
            ->andWhere('b.nom = :bat or p.id IS null')
            ->setParameter('nom', '%'.$nom.'%')
            ->setParameter('etat', $etat)
            ->setParameter('bat', $batiment)
            ->getQuery()
            ->getResult();
    }

    public function getSAStatus(int $SAId)
    {
        return $this->createQueryBuilder('systemAcquisition')
            ->select('systemAcquisition.Etat')
            ->where('systemAcquisition.id = :SAId')
            ->setParameter('SAId', $SAId)
            ->getQuery()
            ->getResult();
    }

    // réucpérer les SA qui ne sont pas attribués à une salle
    // utilisé pour afficher les SA disponibles dans le formulaire d'ajout et modifcation des salles
    public function getAllAvalailableSA()
    {
        return $this->createQueryBuilder('systemAcquisition')
            ->leftJoin('App\Entity\Plan', 'plan', 'WITH', 'plan.SA = systemAcquisition')
            ->where('plan IS NULL OR plan.date_desassociation IS NOT NULL')
            ->orderBy('systemAcquisition.nom', 'ASC')
        ;
    }

    // récupère le premier SA disponible
    // utilisé lors de l'ajout d'un nouveau SA dans le formulaire des salles
    public function getFirstAvailableSA() : ?SystemAcquisition
    {
        return $this->createQueryBuilder('systemAcquisition')
            ->leftJoin('App\Entity\Plan', 'plan', 'WITH', 'plan.SA = systemAcquisition')
            ->where('plan IS NULL')
            ->orderBy('systemAcquisition.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getAllSANameBySalle(int $idSalle)
    {
        return $this->createQueryBuilder('sa')
            ->select('sa.nom AS nomSA', 'etage.nomComplet AS nomEtage')
            ->leftJoin('sa.plan', 'plan')
            ->leftJoin('plan.salle', 'salle')
            ->leftJoin('salle.etage', 'etage')
            ->where('plan.salle = :salleId')
            ->setParameter('salleId', $idSalle)
            ->getQuery()
            ->getResult();

    }

    /**
     * @author Axel, Julien
     * @brief Trouve les SA avec le nom passé dans la barre de recherche.
     * @param string $nomSA Le nom donné dans la barre de recherche
     * @param string $batiment Nom du bâtiment dans lequel on fait la recherche
     * @return array Tous les SA avec un nom similaire
     */
    public function findAllByName(string $nomSA, string $batiment) : array
    {
        return $this->createQueryBuilder('sa')
            ->select('sa.nom', 'sa.id', 'sa.etat', 'sa.dateCreation', 'salle.nom AS nomSalle', 'plan.date_desassociation AS assossie')
            ->leftJoin('sa.plan', 'plan')
            ->leftJoin('plan.salle', 'salle')
            ->leftJoin('salle.etage', 'etage')
            ->leftJoin('etage.batiment', 'batiment')
            ->where('sa.nom LIKE :nom')
            ->andWhere('batiment.nom = :batiment OR plan.id IS NULL')
            ->setParameter('nom', '%' . $nomSA . '%') // Append wildcard to the name
            ->setParameter('batiment', $batiment)
            ->orderBy('salle.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Victor
     * @brief Récupère le premier sa d'une salle
     * @param string $nomSalle = le nom donné dans la barre de recherche
     * @param string $batimentNom : le nom du batiment actuel
     * @return SystemeAcquisition : 
     */
    public function findBySalleName(string $nomSalle, string $batimentNom) : ?SystemAcquisition
    {
        return $this->createQueryBuilder('sa')
            ->leftJoin('sa.plan', 'plan')
            ->leftJoin('plan.salle', 'salle')
            ->leftJoin('salle.etage', 'etage')
            ->leftJoin('etage.batiment', 'batiment')
            ->where('salle.nom = :nomSalle')
            ->andWhere('plan.date_desassociation IS NULL')
            ->andWhere('batiment.nom = :batimentNom')
            ->setParameter('nomSalle', $nomSalle)
            ->setParameter('batimentNom', $batimentNom)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @author Corentin
     * @brief retourne le nombre de SA dont etat est la variable $etat
     * @param string $etat = le nom de l'etat correspondant au SA voulu
     * @return int = le nombre de SA qui ont cet état la
     */
    public function countSAByEtat(string $etat, string $batimentNom) : int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->leftJoin('i.plan', 'plan')
            ->leftJoin('plan.salle', 'salle')
            ->leftJoin('salle.etage', 'etage')
            ->leftJoin('etage.batiment', 'batiment')
            ->where('i.etat = :etat')
            ->andWhere('batiment.nom = :batimentNom')
            ->setParameter('etat', $etat)
            ->setParameter('batimentNom', $batimentNom)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @author Corentin
     * @brief Retourne le nombre de SA dont etat est la variable $etat
     * @param int $salleId L'id de la salle
     * @return array Le nombre de SA qui ont cet état la
     */
    public function findByPlanSalle(int $salleId): array
    {
        return $this->createQueryBuilder('sa')
            ->innerJoin('sa.plan', 'plan')
            ->innerJoin('plan.salle', 'salle')
            ->where('salle.id = :salleId')
            ->setParameter('salleId', $salleId)
            ->getQuery()
            ->getResult();
    }
}
