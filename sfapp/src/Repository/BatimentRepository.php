<?php

namespace App\Repository;

use App\Entity\Batiment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Batiment>
 */
class BatimentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batiment::class);
    }

    /**
     * @author Axel
     * @brief Utilise la fonction native 'findAll()' de symfony, se trouvant
     *        dans le parent de la classe
     * @return array un tableau d'entité Bâtiment
     */
    public function findAllBatiments(): array
    {
        return parent::findAll(); // Appelle la commande de son parent
    }

    /**
     * @autor Côme
     * @brief renvoie l'id, le nom, le nombre d'étages, le nombre de salle et la moyenne des capteurs de température pour chaque batiment
     * @return array
     */
    public function findAll() : array
    {
        $results = $this->createQueryBuilder('batiment')
            ->select(
                'batiment.id',
                'batiment.nom',
                'COUNT(DISTINCT etage.id) AS nbEtages',
                'COUNT(DISTINCT salle.id) AS nbSalles',
                'AVG(capteur.valeur) AS moyenne_capteur')
            ->leftJoin('batiment.etages', 'etage')
            ->leftJoin('etage.salles', 'salle')
            ->leftJoin('salle.plans', 'plan')
            ->leftJoin('plan.SA', 'sa')
            ->leftJoin('sa.capteurs', 'capteur')
            ->where('capteur.type = :type OR etage.id IS NULL OR salle.id IS NULL OR plan.id IS NULL')
            ->setParameter('type', 'temperature')
            ->groupBy('batiment.id')
            ->orderBy('batiment.nom', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($results as &$result) {
            $result['moyenne_capteur'] = round($result['moyenne_capteur'], 1);
        }

        return $results;
    }

    /**
     * @author Axel
     * @brief récupère les bâtiments en fonction du string donné
     *        (utile pour la barre de recherche)
     * @param string $nom = le string
     * @return array = le ou les bâtiments avec un nom similaire
     */
    public function findAllBatimentByName(string $nom): array
    {
        return $this->createQueryBuilder('batiment')
            ->select('batiment.nom, count(DISTINCT etage.id) AS nbEtages, 
                    count(DISTINCT salle.id) AS nbSalles, AVG(capteur.valeur) AS moyenneTemp')
            ->leftJoin('batiment.etages', 'etage')
            ->leftJoin('etage.salles', 'salle')
            ->leftJoin('salle.plans', 'plan')
            ->leftJoin('plan.SA', 'sa')
            ->leftJoin('sa.capteurs', 'capteur', 'WITH', "capteur.type = 'température'")

            ->where('batiment.nom LIKE :nom')
            ->setParameter('nom', '%'.$nom.'%')
            ->groupBy('batiment.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Récupère tout les bâtiments avec leurs détails (température, salles etc)
     * @return array = tout les bâtiments
     */
    public function findAllWithDetails() : array
    {
        return $this->createQueryBuilder('batiment')
            ->select('batiment.nom, count(DISTINCT etage.id) AS nbEtages, 
                    count(DISTINCT salle.id) AS nbSalles, AVG(capteur.valeur) AS moyenneTemp')

            ->leftJoin('batiment.etages', 'etage')
            ->leftJoin('etage.salles', 'salle')
            ->leftJoin('salle.plans', 'plan')
            ->leftJoin('plan.SA', 'sa')
            ->leftJoin('sa.capteurs', 'capteur', 'WITH', "capteur.type = 'température'")

            ->groupBy('batiment.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * @autor Côme
     * @brief renvoie un boolean pour savoir si un batiment a déja le meme nom
     * @param string $nom = nouveau nom du batiment
     * @param int $id = id du batiment concerné
     * @return Batiment|null = renvoie un bool
     */
    public function findByName(string $nom, int $id) : ?Batiment
    {
        return $this->createQueryBuilder('batiment')
            ->where('batiment.nom = :nom')
            ->andWhere('batiment.id != :id')
            ->setParameter('nom', $nom)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @author Corentin
     * @brief Récupère le bâtiment auquel appartient une salle donnée.
     * @param int $salleId = L'ID de la salle.
     * @return Batiment|null = Le bâtiment auquel appartient la salle, ou null si non trouvé.
     */
    public function findBatimentBySalle(int $salleId): ?Batiment
    {
        return $this->createQueryBuilder('batiment')
            ->select('batiment') // On veut seulement le bâtiment
            ->innerJoin('batiment.etages', 'etage') // Joindre les étages du bâtiment
            ->innerJoin('etage.salles', 'salle') // Joindre les salles des étages
            ->where('salle.id = :salleId') // Filtrer par l'ID de la salle
            ->setParameter('salleId', $salleId)
            ->getQuery()
            ->getOneOrNullResult(); // On récupère un seul résultat ou null si pas trouvé
    }




}
