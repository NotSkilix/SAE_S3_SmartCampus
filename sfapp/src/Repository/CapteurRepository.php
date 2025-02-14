<?php

namespace App\Repository;

use App\Entity\Batiment;
use App\Entity\Capteur;
use App\Entity\Salle;
use App\Entity\SystemAcquisition;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Capteur>
 */
class CapteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Capteur::class);
    }

    public function findAllBySA(int $saID)
    {
        return $this->createQueryBuilder('capteurs')
            ->select('capteurs.id, capteurs.valeur, capteurs.type, capteurs.date')
            ->where('capteurs.SA = :saID')
            ->setParameter('saID', $saID)
            ->getQuery()
            ->getResult();
    }

    public function findAllStatusBySA(int $saID)
    {
        return $this->createQueryBuilder('capteurs')
            ->select('capteurs.type, capteurs.etat')
            ->where('capteurs.SA = :saID')
            ->setParameter('saID', $saID)
            ->getQuery()
            ->getResult();
    }

    /**
     * @autor Côme, Axel
     * @brief renvoie un capteur qui appartient à un ESP
     * @param string $nomSA = nom du SA
     * @param Type $type = type du capteur
     * @return Capteur|null le capteur ou null
     */
    public function findCapteur(string $nomSA, Type $type) : ?Capteur
    {
        return $this->createQueryBuilder('capteurs')
            ->join('capteurs.SA', 'SA')
            ->where('SA.nom = :nomSA and capteurs.type = :type')
            ->setParameter('nomSA', $nomSA)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @author Julien
     * @brief Renvoie le type et la valeur des capteurs associés à une salle
     * @param int $salleId Identifiant de la salle souhaitée
     * @return array Liste des types et valeurs des capteurs
     */
    public function findBySalle(int $salleId): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.type', 'c.valeur')
            ->join('c.SA', 'sa')
            ->join('sa.plan', 'p')
            ->join('p.salle', 's')
            ->where('s.id = :salleId')
            ->setParameter('salleId', $salleId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Retourne l'id de la salle qui a comme SA attribué celui du capteur
     * @param int $capteurID le capteur du SA
     * @return int|null la réponse (id de la salle) ou null
     */
    public function getSalleIdByCapteurID(int $capteurID) : ?int
    {
        return $this->createQueryBuilder('capteur')
            ->select('IDENTITY(plan.salle)')
            ->join('capteur.SA', 'SA')
            ->leftJoin('SA.plan', 'plan') // SA peut avoir plusieurs plan
            ->where('capteur.id = :capteurID')
            ->setParameter('capteurID', $capteurID)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @author Axel
     * @brief Récupère tout les SA qui ont une valeur et une date
     * @return array tableau d'entité capteurs
     */
    public function getAllCapteursWithValuesAndDate(): array
    {
        return $this->createQueryBuilder('capteurs')
            ->where('capteurs.valeur is not null')
            ->andWhere('capteurs.date is not null')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Récupère l'id d'un SA en fonction de l'id du capteur
     * @param int $capteurID = l'id du capteur
     * @return int|null l'id du SA ou null
     */
    public function getSAIDByCapteurID(int $capteurID) : ?int
    {
        return $this->createQueryBuilder('capteurs')
            ->select('sa.id')
            ->join('capteurs.SA', 'sa')
            ->where('capteurs.id = :capteurID')
            ->setParameter('capteurID', $capteurID)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
