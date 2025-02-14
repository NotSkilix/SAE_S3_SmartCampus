<?php

namespace App\Command;

use App\Entity\Meteo;
use App\Repository\MeteoRepository;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-weather', // Nom de la commande
    description: 'Récupère les données de la météo pour les mettres en base (Entité Meteo)',
)]
class FetchWeatherCommand extends Command
{
    private WeatherService $weatherService;
    private MeteoRepository $meteoRepository;
    private EntityManagerInterface $manager;

    public function __construct(WeatherService $weatherService, MeteoRepository $meteoRepository, EntityManagerInterface $entityManager)
    {
        $this->weatherService = $weatherService;
        $this->meteoRepository = $meteoRepository;
        $this->manager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Commande fetch weather appelé');

        $time = new \DateTime();
        $time->setTimezone(new \DateTimeZone('Europe/Paris'));
        $time = $time->format('d-m-Y H:i:s');

        try
        {
            $weatherResponse = $this->weatherService->fetchWeather();
            if ($weatherResponse != null)
            {
                $temp = $weatherResponse['main']['temp'];
                $hum = $weatherResponse['main']['humidity'];
                $description = $weatherResponse['weather'][0]['description'];
                $icon = $weatherResponse['weather'][0]['icon'];

                $meteo = $this->meteoRepository->findAll();
                // Si il n'y a pas d'entité météo en base on le crée
                if ($meteo == null)
                {
                    $meteo = new Meteo();
                }
                // Si l'entité est déjà existante on la récupére
                else
                {
                    $meteo = $meteo[0];
                }
                $meteo->setTemp($temp);
                $meteo->setHum($hum);
                $meteo->setDescription($description);
                $meteo->setIcon($icon);

                $this->manager->persist($meteo);
                $this->manager->flush();
            }

        }
        catch (\Exception $exception)
        {
            $io->error('La commande a échoué' . "(" . $time . ")");
            return Command::FAILURE;
        }

        $io->success("La command a été exécuté avec succés (". $time . ")");
        return Command::SUCCESS;
    }
}
