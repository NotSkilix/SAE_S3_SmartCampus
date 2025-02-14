<?php

namespace App\Command;

use App\Entity\Batiment;
use App\Entity\Note;
use App\Entity\TypeConseil;
use App\Entity\TypeNote;
use App\Repository\BatimentRepository;
use App\Repository\ConseilRepository;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/** Constantes */
const MAX_CONSEIL_BATIMENT = 2; // Le nombre maximum de conseil du même type dans un bâtiment
const MAX_INTERVAL_NOTE = 60 * 6; // Le maximum de minutes (60sec * 6 = 1h) qu'une note peut être ignorée / lue avant qu'une nouvelle prend sa place
#[AsCommand(
    name: 'app:check-conseils', // commande à exécuter
    description: 'Vérifie le nombre de conseils se trouvant dans un bâtiment, si beaucoup sont du même type, crée une note
                  générale de type ProblemeBatiment pour le chargé de mission',
)]
class CheckNbConseilsCommand extends Command
{
    private BatimentRepository $batimentRepository; // Permet de récupérer les bâtiments à check
    private ConseilRepository $conseilRepository; // Permet de récupérer les conseils du bâtiment
    private EntityManagerInterface $entityManager; // Permet de persist et flush les nouvelles notes s'il y en a
    private NoteRepository $noteRepository; // Permet de vérifier l'existance d'une note similaire à celle qu'on essaie de créer

    private int $nbNoteCreated = 0; // Nombre de notes créé, initialisé à 0 au départ.
    private array $listBatimentNote = []; // La liste des bâtiments ayant reçue une nouvelle note
    /**
     * @author Axel
     * @brief Constructeur de la classe, récupère tous les répository nécessaires au fonctionnement de la commande
     * @param BatimentRepository $batimentRepository Récupère le BatimentRepository
     * @param ConseilRepository $conseilRepository Récupère le ConseilRepository
     * @param EntityManagerInterface $entityManager Récupère le EntityManagerInterface
     * @param NoteRepository $noteRepository Récupère le NoteRepository
     */
    public function __construct(BatimentRepository $batimentRepository, ConseilRepository $conseilRepository, EntityManagerInterface $entityManager, NoteRepository $noteRepository)
    {
        $this->batimentRepository = $batimentRepository;
        $this->conseilRepository = $conseilRepository;
        $this->entityManager = $entityManager;
        $this->noteRepository = $noteRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
//        $this
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
    }

    /**
     * @author Axel
     * @brief Ce qui est exécuté lors de l'appel de la commande
     * @param InputInterface $input pour les retours lors de l'exécution
     * @param OutputInterface $output pour les retours lors de l'exécution
     * @return int le code de réussite ou échec
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Vérifications des conseils');

        $time = new \DateTime();
        $time->setTimezone(new \DateTimeZone('Europe/Paris'));
        $time = $time->format('d-m-Y H:i:s');

        try
        {
            $listBatiments = $this->batimentRepository->findAllBatiments();

            // Si on trouve bien des bâtiments
            if(count($listBatiments) != 0)
            {
                // On parcourt les bâtiments pour avoir la liste des conseils et vérifier si beaucoup sont du même type
                foreach ($listBatiments as $batiment)
                {
                    $listConseils = $this->conseilRepository->getAllByBatiment($batiment);

                    // Si on trouve des conseils
                    if(count($listConseils) != 0)
                    {
                        // Initialse les variables pour connaître le nombre de conseils par bâtiments
                        $nbConseilTemp = 0;
                        $nbConseilCo2 = 0;
                        $nbConseilHum = 0;
                        $nbConseilLum = 0;
                        $nbConseilGpu = 0;
                        foreach ($listConseils as $conseil)
                        {
                            // Suivant le type ajoute + 1 au nombre de conseils
                            switch ($conseil->getType())
                            {
                                case TypeConseil::temp:
                                    $nbConseilTemp++;
                                    break;
                                case TypeConseil::co2:
                                    $nbConseilCo2++;
                                    break;
                                case TypeConseil::hum:
                                    $nbConseilHum++;
                                    break;
                                case TypeConseil::lum:
                                    $nbConseilLum++;
                                    break;
                                case TypeConseil::gpu:
                                    $nbConseilGpu++;
                                    break;
                                default: // Si on ne connaît pas un type (impossible normalement)
                                    break;
                            }
                        }
                        $this->checkOutOfRange($batiment, $nbConseilTemp, $nbConseilCo2, $nbConseilHum, $nbConseilLum, $nbConseilGpu);
                    }
                }
            }
            else
            {
                throw new \Exception("Aucun bâtiment trouvé, impossible d'éxécuter la commande");
            }
        }
        catch (\Exception $exception)
        {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $io->success('Vérifications des conseils réussies! (' . $this->nbNoteCreated . ' notes ont été créées à '. $time . ')');
        $io->note("Bâtiments ayant reçue une note: ");
        foreach ($this->listBatimentNote as $batiment)
        {
            $io->writeln($batiment->getNom());
        }
        return Command::SUCCESS;
    }

    /**
     * @author Axel
     * @brief Vérifie si l'on dépasse la limite de conseils par bâtiment
     * @param Batiment $batiment le bâtiment actuellement testé
     * @param int $nbConseilTemp nombre de conseils de type température
     * @param int $nbConseilCo2 nombre de conseils de type CO2
     * @param int $nbConseilHum nombre de conseils de type humidité
     * @param int $nbConseilLum nombre de conseils de type luminosité
     * @param int $nbConseilGpu nombre de conseils de type gpu
     * @return void
     */
    private function checkOutOfRange(Batiment $batiment,int $nbConseilTemp, int $nbConseilCo2, int $nbConseilHum, int $nbConseilLum, int $nbConseilGpu)
    {
        // TO DO:
        // Gérer la température (peut être trop froid ou trop chaud)

        // Température
        if($nbConseilTemp > MAX_CONSEIL_BATIMENT)
        {
            // Si il y a pas une note similaire
            if(!$this->doesSimilarNoteExist("salles ont un problèmes de température", $batiment))
            {
                $texte = $nbConseilTemp . " salles ont un problèmes de température";
                $conseil = "Vérifier l'isolation des salles";

                $this->makeNewNote($texte, $conseil, $batiment);
                $this->nbNoteCreated++; // Ajout 1 au nombre de notes crée
            }
        }
        // Humidité
        if($nbConseilHum > MAX_CONSEIL_BATIMENT)
        {
            // Si il y a pas une note similaire
            if(!$this->doesSimilarNoteExist("salles sont trop humides", $batiment))
            {
                $texte = $nbConseilHum . " salles sont trop humides";
                $conseil = "Vérifier l'isolation du bâtiment";
                $this->makeNewNote($texte, $conseil, $batiment);
                $this->nbNoteCreated++; // Ajout 1 au nombre de notes crée
            }
        }

        // CO2
        if($nbConseilCo2 > MAX_CONSEIL_BATIMENT)
        {
            // Si il y a pas une note similaire
            if(!$this->doesSimilarNoteExist("salles ont un taux de Co2 élevé", $batiment))
            {
                $texte = $nbConseilHum . " salles ont un taux de Co2 élevé";
                $conseil = "Vérifier la ventilation des salles";
                $this->makeNewNote($texte, $conseil, $batiment);
                $this->nbNoteCreated++; // Ajout 1 au nombre de notes crée
            }
        }

        // Luminosité
        if($nbConseilHum > MAX_CONSEIL_BATIMENT)
        {
            // Si il y a pas une note similaire
            if(!$this->doesSimilarNoteExist("salles sont resté allumées", $batiment))
            {
                $texte = $nbConseilHum . " salles sont resté allumées";
                $conseil = "Notifié les élèves de leurs conduites peu écologique";
                $this->makeNewNote($texte, $conseil, $batiment);
                $this->nbNoteCreated++; // Ajout 1 au nombre de notes crée
            }
        }

        /**
         * TO DO: GPU
         * Aucun conseil GPU envoyé, à faire une fois que ce sera le cas
         */

    }

    /**
     * @author Axel
     * @brief Crée une nouvelle note de type 'ProblemeBatimentNonEnvoye' avec comme attribut un batiment (celui actuellement itéré),
     *        un texte dépendant du type de conseil affiché, et le potentiel conseil par défaut.
     * @param string $texte le texte expliquant la raison de la note (ex: 11 salles trop chaudes)
     * @param string $conseil  le conseil par défaut que le technicien pourrait réaliser
     * @param Batiment $batiment le bâtiment dans lequel les problèmes ont lieux
     * @return void
     */
    private function makeNewNote(string $texte, string $conseil, Batiment $batiment) : void
    {
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone('Europe/Paris'));

        $note = new Note();
        $note->setTitre("Probleme Batiment");
        $note->setType(TypeNote::ProblemeBatimentNonEnvoye);
        $note->setDate($date); // Date de l'exécution de la commande
        $note->setBatiment($batiment);
        $note->setTexte($texte);
        $note->setConseil($conseil);

        // Si un bâtiment avec le même nom a déjà une note, alors on insère pas à nouveau le bâtiment dans la liste
        $isBatimentWithoutNote = true;
        foreach($this->listBatimentNote as $batimentWithNote)
        {
            if ($batimentWithNote->getNom() == $batiment->getNom())
            {
                $isBatimentWithoutNote = false;
            }
        }
        // Le bâtiment a aucune note
        if ($isBatimentWithoutNote)
        {
            $this->listBatimentNote[] = $batiment;
        }

        $this->entityManager->persist($note);
        $this->entityManager->flush();
    }

    /**
     * @author Axel
     * @brief Vérifie si une note similaire existe déjà, si son type n'est pas 'ProblemeBatimentNonEnvoye'
     *        et si son intervale de temps n'est pas supérieure au maximum après avoir été lue ou ignorée
     * @param string $texte le texte a comparé pour voir si il y en a des similaires
     * @param Batiment $batiment le batiment où l'on fait nos check
     * @return bool 0: aucun conseil similaire, on en fait un nouveau
     *              1: Un conseil est déjà affiché ou a été ignoré / lu récemment
     */
    private function doesSimilarNoteExist(string $texte, Batiment $batiment) : bool
    {
        $similarNotes = $this->noteRepository->getNoteByTexteAndBatiment($texte, $batiment);

        // Si il y a une note similaire
        if(count($similarNotes) != 0)
        {
            $latestNote = $similarNotes[0]; // Prend la dernière note
            $noteDate = $latestNote->getDate(); // Date de la note
            $currentDate = new \DateTime(); // Date actuelle (à l'exécution de la commande)
            $currentDate->setTimezone(new \DateTimeZone('Europe/Paris'));

            // Calcul la différence en seconde
            $intervalInSeconds = abs($noteDate->getTimestamp() - $currentDate->getTimestamp());

            // Si la note est affiché au chargé de mission
            if($latestNote->getType() == TypeNote::ProblemeBatimentNonEnvoye)
            {

                return true;
            }
            // Sinon si l'intervale ne dépasse pas la limite il y a toujours un conseil
            elseif ($intervalInSeconds < MAX_INTERVAL_NOTE)
            {
                return true;
            }
        }
        return false;
    }
}
