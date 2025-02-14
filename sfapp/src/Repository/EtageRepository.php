<?php

namespace App\Repository;

use App\Entity\Etage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etage>
 */
class EtageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etage::class);
    }

    /**
     * Récupère tout les étages et les tries par l'id
     * pour avoir le RDC en premier choix
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('e')
            ->orderby('e.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Victor
     * @brief récupère les étages d'un batiment pour le formulaire
     *          de création d'une salle et modif
     * @param string $nomBatiment : le nom du batiment
     * @return query
     */
    public function findAllByBatimentForm(string $nomBatiment)
    {
        return $this->createQueryBuilder('etage')
            ->join('etage.batiment', 'batiment')
            ->andWhere('batiment.nom = :val')
            ->setParameter('val', $nomBatiment)
            ->orderby('batiment.id')
        ;
    }

    public function findAllByBatiment(string $nomBatiment) : array
    {
        return $this->createQueryBuilder('etage')
            ->join('etage.batiment', 'batiment')
            ->andWhere('batiment.nom = :val')
            ->setParameter('val', $nomBatiment)
            ->orderby('batiment.id')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllByBatimentId(int $idBatiment) : array
    {
        return $this->createQueryBuilder('etage')
            ->join('etage.batiment', 'batiment')
            ->andWhere('batiment.id = :val')
            ->setParameter('val', $idBatiment)
            ->orderby('batiment.id')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByName(string $nom, int $idEtage, int $idBatiment) : array
    {
        return $this->createQueryBuilder('etage')
            ->join('etage.batiment', 'batiment')
            ->where('etage.nomComplet = :nom and etage.id != :idEtage and batiment.id = :idBatiment')
            ->setParameter('nom', $nom)
            ->setParameter('idEtage', $idEtage)
            ->setParameter('idBatiment', $idBatiment)
            ->getQuery()
            ->getResult();
    }

    public function CountSalleForOneEtage(int $id): int
    {
        return $this->createQueryBuilder('etage')
            ->select('count(salle.id)')
            ->leftJoin('etage.salles','salle')
            ->where('etage.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
