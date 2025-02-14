<?php

namespace App\Command;

use App\Entity\Type;
use App\Repository\CapteurRepository;
use App\Service\SensorDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\DateTime;
use function PHPUnit\Framework\isEmpty;

/** Constantes */
const SA_NAME = [
    'ESP-004', // D205
    'ESP-008', // D206
    'ESP-006', // D207
    'ESP-014', // D204
    'ESP-012', // D203
    'ESP-005', // D303
    'ESP-011', // D304
    'ESP-007', // C101
    'ESP-024', // D109
    'ESP-026', // Secrétariat
    'ESP-030', // D001
    'ESP-028', // D002
    'ESP-020', // D004
    'ESP-021', // C004
    'ESP-022', // C007
];

const DATABASE_NAME = [
    'sae34bdk1eq1', // D205
    'sae34bdk1eq2', // D206
    'sae34bdk1eq3', // D207
    'sae34bdk2eq1', // D204
    'sae34bdk2eq2', // D203
    'sae34bdk2eq3', // D303
    'sae34bdl1eq1', // D304
    'sae34bdl1eq2', // C101
    'sae34bdl1eq3', // D109
    'sae34bdl2eq1', // Secrétariat
    'sae34bdl2eq2', // D001
    'sae34bdl2eq3', // D002
    'sae34bdm1eq1', // D004
    'sae34bdm1eq2', // C004
    'sae34bdm1eq3', // C007
];

const SENSOR_NAME = [
    "temp",
    "hum",
    "co2",
    "lum",
];

#[AsCommand(
    name: 'app:fetch-sensors-data', // nom de la commande a exécuter
    description: 'Récupère les données de lAPI des capteurs',
)]
class FetchSensorsDataCommand extends Command
{
    private SensorDataService $sensorsService; // Service a appeler
    private CapteurRepository $capteurRepository; // Pour récupérer le capteur assigné dans notre base
    private EntityManagerInterface $manager; // Pour persist en base

    /**
     * @author Axel
     * @brief constructeur de la commande permettant de récuperer le service à appeler
     * @param SensorDataService $sensorsService le service des appels vers l'API
     * @param CapteurRepository $capteurRepository le répository des capteurs
     * @param EntityManagerInterface $entityManager le manager pour persist les données en base
     */
    public function __construct(SensorDataService $sensorsService, CapteurRepository $capteurRepository, EntityManagerInterface $entityManager)
    {
        $this->sensorsService = $sensorsService;
        $this->capteurRepository = $capteurRepository;
        $this->manager = $entityManager;

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
     * @brief l'exécution de la commande permettant d'ajouter en base les dernières données de chaque salle
     * @param InputInterface $input pour les retours lors de l'exécution
     * @param OutputInterface $output pour les retours lors de l'exécution
     * @return int le code de réussite ou échec
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Commande fetch sensors data appelé');

        $time = new \DateTime();
        $time->setTimezone(new \DateTimeZone('Europe/Paris'));
        $time = $time->format('d-m-Y H:i:s');

        try
        {
            // Parcours chaque base
            for ($i = 0; $i < sizeof(SA_NAME); $i++)
            {
                // Essaie de récupérer les dernières valeurs des capteurs de la base
                for ($j = 0; $j < sizeof(SENSOR_NAME); $j++)
                {
                    try
                    {
                    $reponse = $this->sensorsService->fetchSensorData(DATABASE_NAME[$i], SA_NAME[$i], SENSOR_NAME[$j]);

                    // Si la réponse n'est pas vide et qu'il n'est pas vide
                    if ($reponse != null || sizeof($reponse) != 0)
                    {
                        // Récupère le type pour la querry
                        $typeCapteur = null;
                        switch (SENSOR_NAME[$j])
                        {
                            case "temp":
                                $typeCapteur = Type::temperature;
                                break;
                            case "hum":
                                $typeCapteur = Type::humidite;
                                break;
                            case "co2":
                                $typeCapteur = Type::co2;
                                break;
                            case "lum":
                                $typeCapteur = Type::luminosite;
                                break;
                        }
                        // Si le type n'est pas null
                        if ($typeCapteur != null)
                        {
                                $capteur = $this->capteurRepository->findCapteur(SA_NAME[$i], $typeCapteur);

                                $valeur = (float) $reponse[0]["valeur"];
                                $capteur->setValeur($valeur);



                                $date = new \DateTime($reponse[0]["dateCapture"]);
                                $capteur->setDate($date);

                                $this->manager->persist($capteur);
                                $this->manager->flush();
                            }
                        }
                    }
                    catch (\Exception $e)
                    {
                        $io->error($e->getMessage());
                        continue;
                    }
                }
            }
        }
        catch (\Exception $exception)
        {
            $io->error($exception->getMessage() . "(" . $time . ")");
        }

        $io->success("Commande appelé avec succès (". $time . ")");
        return Command::SUCCESS;
    }
}
