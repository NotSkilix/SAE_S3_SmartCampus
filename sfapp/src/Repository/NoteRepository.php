<?php

namespace App\Repository;

use App\Entity\Batiment;
use App\Entity\Note;
use App\Entity\TypeNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    //    /**
    //     * @return Note[] Returns an array of Note objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Note
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySalle(string $batimentNom, int $idSalle): array
    {
        return $this->createQueryBuilder('note')
            ->join('note.salle', 'salle')
            ->join('salle.etage', 'etage')
            ->join('etage.batiment', 'batiment')
            ->where('salle.id = :idSalle and batiment.nom = :batimentNom')
            ->setParameter('idSalle', $idSalle)
            ->setParameter('batimentNom', $batimentNom)
            ->orderby('note.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id): ?Note
    {
        return $this->createQueryBuilder('note')
            ->where('note.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllProbleme(string $batimentNom): array
    {
        return $this->createQueryBuilder('note')
            ->join('note.salle', 'salle')
            ->join('salle.etage', 'etage')
            ->join('etage.batiment', 'batiment')
            ->where('batiment.nom = :batimentNom and note.type = :Probleme')
            ->setParameter('batimentNom', $batimentNom)
            ->setParameter('Probleme', TypeNote::class::Probleme->value)
            ->orderBy('note.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @author Axel
     * @brief Récupère tous les problèmes d'un batiment suivant son type passé en paramètre.
     *        Permet de filtrer parmi tous les types possibles d'un problème bâtiment
     * @param string $batimentNom nom du batiment dans lequel on cherche un problème
     * @param TypeNote $type le type de la note (voir enumération pour les types possibles)
     * @return array la réponse sous forme de tableau
     */
    public function findAllProblemeBatimentByType(TypeNote $type, string $batimentNom): array
    {
        return $this->createQueryBuilder('note')
            ->select('note.id', 'note.titre', 'note.texte', 'note.conseil', 'count(note.id) as nbNotes')
            ->join('note.batiment', 'batiment')
            ->where('note.type = :type')
            ->andWhere('batiment.nom = :batimentNom')
            ->setParameter('batimentNom', $batimentNom)
            ->setParameter('type', $type->value)
            ->groupBy('note.id')
            ->getQuery()
            ->getResult();
    }

    public function getNoteByTexteAndBatiment(string $texte, Batiment $batiment): array
    {
        return $this->createQueryBuilder('note')
            ->WHERE('note.texte LIKE :texte')
            ->andWhere('note.batiment = :batiment')
            ->setParameter('texte', '%' . $texte . '%') // Wildcards pour trouver des textes similaires
            ->setParameter('batiment', $batiment)
            ->orderBy('note.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByType($type, string $batimentNom, int $salle): array
    {
        return $this->createQueryBuilder('note')
            ->join('note.salle', 'salle')
            ->join('salle.etage', 'etage')
            ->join('etage.batiment', 'batiment')
            ->where('salle.id = :idSalle and batiment.nom = :batimentNom and note.type = :type')
            ->setParameter('idSalle', $salle)
            ->setParameter('batimentNom', $batimentNom)
            ->setParameter('type', $type)
            ->orderby('note.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
