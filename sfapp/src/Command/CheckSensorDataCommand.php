<?php

namespace App\Command;
use App\Entity\Capteur;
use App\Entity\Note;
use App\Entity\SystemAcquisition;
use App\Entity\Type;
use App\Entity\TypeNote;
use App\Repository\CapteurRepository;
use App\Repository\NoteRepository;
use App\Repository\SalleRepository;
use App\Repository\SystemAcquisitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function PHPUnit\Framework\isEmpty;

/** Constantes */
const MAX_TIMELAPSE = 1; // Maximum de différence de temps qu'un capteur peut avoir (1h)

#[AsCommand(
    name: 'app:check-sensor-data', // la commande à exécuter
    description: 'Vérifie les valeurs des capteurs pour détecter une anomalie',
)]
class CheckSensorDataCommand extends Command
{
    private CapteurRepository $capteurRepository; // Pour récupérer les capteurs
    private SystemAcquisitionRepository $systemAcquisitionRepository; // Pour récupérer le SA
    private SalleRepository $salleRepository; // Pour récupérer la salle
    private EntityManagerInterface $manager; // Pour persist et flush les notes

    private NoteRepository $noteRepository;
    private int $nbNewNotes = 0; // nombres de notes ajoutées

    // TO DO: le commentaire
    public function __construct(CapteurRepository $capteurRepository, SystemAcquisitionRepository $systemAcquisitionRepository, SalleRepository $salleRepository, EntityManagerInterface $manager, NoteRepository $noteRepository)
    {
        $this->capteurRepository = $capteurRepository;
        $this->systemAcquisitionRepository = $systemAcquisitionRepository;
        $this->salleRepository = $salleRepository;
        $this->noteRepository = $noteRepository;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure(): void
    {
//        $this
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Exécution de la commande 'CheckSensorData'");

        $time = new \DateTime();
        $time->setTimezone(new \DateTimeZone('Europe/Paris'));
        $time = $time->format('Y-m-d H:i:s');


        try
        {
            $capteurs = $this->capteurRepository->getAllCapteursWithValuesAndDate(); // Récupère les capteurs avec une date et une valeure
            // Si il y a des capteurs
            if (count($capteurs) > 0)
            {
                // Parcours les capteurs pour avoir leur date d'envoi de données
                foreach ($capteurs as $capteur)
                {
                    // Si la date de la capture est en dehors du maximum entre la date actuelle
                    if ($this->isCapteurOuOfTimelapse($capteur))
                    {
                        $this->makeNote($capteur);
                    }
                }

                $this->manager->flush();
            }
        }
        catch (\Exception $exception)
        {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $io->success("Exécution de la commande réussite (". $this->nbNewNotes ." notes ont été ajoutées. " . $time . ")");
        return Command::SUCCESS;
    }

    /**
     * @param Capteur $capteur l'entité capteur
     * @return bool 0: la dernière valeur se trouve dans les temps (pas plus d'1h depuis la dernière valeure)
     *              1: la dernière valeur ne se trouve pas dans les temps
     * @author Axel
     * @brief Vérifie si la date du capteur est en dehors du maximum entre chaque capture
     */
    private function isCapteurOuOfTimelapse(Capteur $capteur): bool
    {
        $dateCapteur = $capteur->getDate();
        $currentDate = new \DateTime();
        $currentDate->setTimezone(new \DateTimeZone('Europe/Paris'));

        // Compare l'heure actuelle à celle de la dernière prise, renvoie la différence en seconde
        $diffDate = $dateCapteur->diff($currentDate);


//        // Si l'intervale de la dernière prise est supérieure au maximum
        if ($diffDate->h > MAX_TIMELAPSE)
        {
            return true;
        }

        return false;
    }

    private function makeNote(Capteur $capteur): void
    {
        $currentDate = new \DateTime();
        $currentDate->setTimezone(new \DateTimeZone('Europe/Paris'));

        // Récupère l'id du SA
        $saID = $this->capteurRepository->getSAIDByCapteurID($capteur->getId());
        if ($saID == null)
        {
            throw new \Exception("Impossible de trouvé un SA lié à ce capteur (" . $capteur->getId() . ")");
        }

        // Récupère le SA
        $SA = $this->systemAcquisitionRepository->find($saID);
        if (!$SA) {
            throw new \Exception("Impossible de trouvé un SA avec cette id (" . $saID . ")");
        }

        // Récupère la salle
        $salle = $this->salleRepository->findSalleBySAId($SA->getId());
        if (!$salle)
        {
            // Si on ne trouve pas de salle ça signifie que c'est une fixture
            return;
        }


        $texte = "Le SA " . $SA->getNom() . " n'a pas envoyé de " . $capteur->getType()->value . " depuis plus d'une heure";
        $similarNote = $this->noteRepository->findBy(['texte' => $texte]);
        if(sizeof($similarNote) > 0)
        {
            // Si le problème est affiché et non lue ni ignoré on sort
            if ($similarNote[0]->getType() == TypeNote::Probleme)
            {
                return;
            }
            // Si la date n'est pas de TypeNote::problème et qu'elle a été créer depuis plus d'une heure on la réaffiche
            else if ($similarNote[0]->getDate()->diff($currentDate)->h > MAX_TIMELAPSE)
            {
                $similarNote[0]->setType(TypeNote::Probleme);
                return;
            }
        }


        $note = new Note();
        $note->setTitre("Problème");
        $note->setTexte($texte);
        $note->setType(TypeNote::Probleme);
        $note->setDate(new \DateTime());
        $note->setSalle($salle);

        $this->nbNewNotes++;

        $this->manager->persist($note);
    }
}
