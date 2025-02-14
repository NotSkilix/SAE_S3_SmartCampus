<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:run-scheduler', // nom de la commande a exécuter
    description: 'Active le scheduler pour update toutes les x minutes la base avec les API',
)]
class SchedulerCommand extends Command
{

    private const INTERVAL_MINUTES = 10;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Active le scheduler');
        $io->success('Sceduler commencé. CTRL+C pour arrêter');

        // Quand activé tourne à l'infini est update toutes les xmin
        while (true)
        {
            // Lance la commande pour la météo
            $process = new Process(['php', 'bin/console', 'app:fetch-weather']);
            $process->run();
            $output->writeln($process->getOutput()); // envoie dans la console la réponse de la commande

            // Lance la commande pour les données des capteurs
            $process = new Process(['php', 'bin/console', 'app:fetch-sensors-data']);
            $process->run();
            $output->writeln($process->getOutput()); // envoie dans la console la réponse de la commande


            // Lance la commande pour les conseils
            $process = new Process(['php', 'bin/console', 'app:check-conseils']);
            $process->run();
            $output->writeln($process->getOutput()); // envoie dans la console la réponse de la commande

            // Lance la commande pour les notes
            $process = new Process(['php', 'bin/console', 'app:check-sensor-data']);
            $process->run();
            $output->writeln($process->getOutput()); // envoie dans la console la réponse de la commande

            // Attend l'intervale en minute (10min par défaut)
            sleep(self::INTERVAL_MINUTES * 60);
        }
    }
}
