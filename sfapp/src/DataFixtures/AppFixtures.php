<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Capteur;
use App\Entity\Exposition;
use App\Entity\Frequentation;
use App\Entity\Note;
use App\Entity\Salle;
use App\Entity\SystemAcquisition;
use App\Entity\Etage;
use App\Entity\Plan;
use App\Entity\DetailsSalle;
use App\Entity\Etat;
use App\Entity\Type;
use App\Entity\TypeNote;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use PhpParser\Builder\Enum_;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** ============= Constantes ============= */
const maxSallesEtages = 14;

const nbSA = 400;

const nomSA = [
    'ESP-004',
    'ESP-008',
    'ESP-006',
    'ESP-014',
    'ESP-012',
    'ESP-005',
    'ESP-011',
    'ESP-007',
    'ESP-024',
    'ESP-026',
    'ESP-030',
    'ESP-028',
    'ESP-020',
    'ESP-021',
    'ESP-022',
];

const nomEtage = [
    "Rez de chaussée",
    "étage 1",
    "étage 2",
    "étage 3",
    "étage 4",
];

// Tableau nom de salles en fonction de l'étage
const nomSalle = [
    "0" => [
        "D001", "D002", "D003", "D004", "D005", "D006", "D007",
        "C001", "C002", "C003", "C004", "C005", "C006", "C007",
    ],
    "1" => [
        "D101", "D102", "D103", "D104", "D105", "D106", "D107",
        "C101", "C102", "C103", "C104", "C105", "C106", "C107",
    ],
    "2" => [
        "D201", "D202", "D203", "D204", "D205", "D206", "D207",
        "C201", "C202", "C203", "C204", "C205", "C206", "C207",
    ],
    "3" => [
        "D301", "D302", "D303", "D304", "D305", "D306", "D307",
        "C301", "C302", "C303", "C304", "C305", "C306", "C307",
    ],
    "4" => [
        "D401", "D402", "D403", "D404", "D405", "D406", "D407",
        "C401", "C402", "C403", "C404","C405", "C406", "C407",
    ]
];

const descriptionSA = [
    NULL,
    "Installé sur la table du professeur",
    "Installé au fond à gauche de la salle",
    "Capteur manquant"
];

const logins = [
    ["technicien", "smart-campus", "Technicien", ["ROLE_TECHNICIEN"]],
    ["chargemission", "smart-campus", "Chargé de mission", ["ROLE_CHARGEMISSION"]]
];

class AppFixtures extends Fixture
{
    // Interface pour hacher les mots de passe
    private $passwordHasher;

    private $kernel;

    // Tableau des salles avec le bâtiment comme premier argument suivi de la salle
    private array $listSalles = [];
    // Tableau des SA
    private array $listSA = [];

    /**
     * @author Julien
     * @brief Constructeur pour pouvoir injecter les interfaces nécessaires.
     * @param UserPasswordHasherInterface $userPasswordHasherInterface
     */
    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface, KernelInterface $kernel)
    {
        // Injection de l'interface pour hacher les mots de passe
        $this->passwordHasher = $userPasswordHasherInterface;
        $this->kernel = $kernel;
    }

    /**
     * @author Axel
     * @brief charge les fixtures pour le site
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        // Récupère les énums
        $exposition = Exposition::cases();
        $frequentation = Frequentation::cases();
        $etat = Etat::cases();

        $this->getWeather($this->kernel);

        $this->loadBuildingFixturesInfo($manager, $exposition, $frequentation, 4, $etat);

        $this->loadAllBuildingFixtures($manager, $exposition, $frequentation, $etat);

        // Génère les fixtures pour les utilisateurs
        $this->generateUsers($manager);

        $manager->flush();
    }
    /**
     * @author Corentin
     * @brief charge les fixtures pour tous les batiments sauf informatique
     * @param ObjectManager $manager L'object manager utilisé dans le load()
     * @param array $exposition Le tableau des énums pour les expositions possibles
     * @param array $frequentation Le tableau des énums pour la fréquentation
     * @param array $etat tableau de type d'etat possible
     */
    public function loadAllBuildingFixtures(ObjectManager $manager, array $exposition, array $frequentation, array $etat): void
    {
        // Générations des SA
        for ($i = 0; $i <= nbSA; $i++) {
            $isUnique = false;
            $newNom = '';

            // Génère un nom unique pour le SA
            while (!$isUnique) {
                $newNom = "ESP-" . rand(1000, 9800);
                $isUnique = true;

                // Vérifie si le nom est déjà utilisé
                foreach ($this->listSA as $existingSA) {
                    if ($existingSA->getNom() === $newNom) {
                        $isUnique = false;
                        break;
                    }
                }
            }

            // Crée le SA avec un nom unique
            $SA = new SystemAcquisition();
            $SA->setNom($newNom);
            $SA->setEtat($etat[array_rand($etat)]);
            $SA->setDescription(descriptionSA[array_rand(descriptionSA)]);
            $SA->setDateCreation($this->getRandomDate('2024-01-01'));
            $manager->persist($SA);
            $this->listSA[] = $SA;

            $this->generateSensors($SA, $manager);
        }
        // Génère les fixtures pour le bâtiment Génie Civil
        $this->loadBuildingFixtures($manager, $exposition, $frequentation, "Génie-Civil", 4);


        // Génère les fixtures pour le bâtiment Génie Bio
        $this->loadBuildingFixtures($manager, $exposition, $frequentation, "Génie-Bio", 3);


        // Génère les fixtures pour le bâtiment Technique de co
        $this->loadBuildingFixtures($manager, $exposition, $frequentation, "Technique de co", 2);


        $manager->flush();

    }

    /**
     * @author Axel
     * @brief Crée des fixtures pour les bâtiments.
     *        Permet qu'elle soit plus réaliste vu qu'on est dans ce bâtiment.
     * @param ObjectManager $manager L'object manager utilisé dans le load()
     * @param array $exposition Le tableau des énum pour l'exposition
     * @param array $frequentation Le tableau des énums pour la fréquentation
     * @param string $nomBatiment Le nom du bâtiment
     * @param int $nbEtages Nombres d'étages
     */
    public function loadBuildingFixtures(ObjectManager $manager, array $exposition,array $frequentation, string $nomBatiment, int $nbEtages): void
    {
        // Clear la liste des salles
        $this->listSalles = array();

        $batiment = new Batiment();
        $batiment->setNom($nomBatiment);
        $manager->persist($batiment);

        // Génération des étages et des salles:
        for($i = 1; $i <= $nbEtages; $i++)
        {
            $etage = new Etage();
            $etage->setBatiment($batiment);
            $etage->setNomComplet(nomEtage[$i-1]);
            $manager->persist($etage);

            // Génére un nombre aléatoire de salle dans l'étage
            for($j = rand(1, maxSallesEtages); $j < maxSallesEtages; $j++)
            {
                $salle = new Salle();
                $salle->setNom(nomSalle[$i][$j]);
                $salle->setEtage($etage);
                $manager->persist($salle);
                $this->listSalles[] = $salle;


                // Détails de la salle
                $detailsSalle = new DetailsSalle();
                $detailsSalle->setSalle($salle);
                $detailsSalle->setDateDeCreation($this->getRandomDate('2024-01-01'));
                $detailsSalle->setExposition($exposition[array_rand($exposition)]);
                $detailsSalle->setPorte(rand(1,2));
                $detailsSalle->setFenetre(rand(1,5));
                $detailsSalle->setRadiateur(rand(1,5));
                $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
                $detailsSalle->setSuperficie(rand(5,30));
                $manager->persist($detailsSalle);
            }
        }


        $this->generatePlan($manager);
    }
    /**
     * @author Corentin
     * @brief Crée des fixtures pour le bâtiment informatique.
     *        Permet qu'elle soit plus réaliste vu qu'on est dans ce bâtiment.
     * @param ObjectManager $manager L'object manager utilisé dans le load()
     * @param array $exposition Le tableau des énum pour l'exposition
     * @param array $frequentation Le tableau des énums pour la fréquentation
     * @param int $nbEtages Nombres d'étages
     */
    public function loadBuildingFixturesInfo(ObjectManager $manager, array $exposition,array $frequentation, int $nbEtages, array $etat): void
    {
        foreach (nomSA as $nomSA)
        {
            $SA = new SystemAcquisition();
            $SA->setNom($nomSA);
            $SA->setEtat(Etat::Fonctionnel);
            $SA->setDescription(descriptionSA[array_rand(descriptionSA)]);
            $SA->setDateCreation($this->getRandomDate('2024-01-01'));
            $manager->persist($SA);

            $dateCreationSA = $SA->getDateCreation();
            $formatedDate = $dateCreationSA->format('Y-m-d H:i:s'); // Convertie en string
            $dateValue = $this->getRandomDate($formatedDate); // Pour avoir une date random

            $this->listSA[] = $SA;
            $this->generateSensorsInfo($SA, $manager);
        }

        $this->listSalles = array();

        $batiment = new Batiment();
        $batiment->setNom('Informatique');
        $manager->persist($batiment);

        //--------------------------------------------
        //LISTE ETAGE

        $etageRDC = new Etage();
        $etageRDC->setBatiment($batiment);
        $etageRDC->setNomComplet(nomEtage[0]);
        $manager->persist($etageRDC);

        $etage1 = new Etage();
        $etage1->setBatiment($batiment);
        $etage1->setNomComplet(nomEtage[1]);
        $manager->persist($etage1);

        $etage2 = new Etage();
        $etage2->setBatiment($batiment);
        $etage2->setNomComplet(nomEtage[2]);
        $manager->persist($etage2);

        $etage3 = new Etage();
        $etage3->setBatiment($batiment);
        $etage3->setNomComplet(nomEtage[3]);
        $manager->persist($etage3);

        //--------------------------------------------
        //SALLE

        $salleC004 = new Salle();
        $salleC004->setNom('C004');
        $salleC004->setEtage($etageRDC);
        $manager->persist($salleC004);
        $this->listSalles[] = $salleC004;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleC004);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleC007 = new Salle();
        $salleC007->setNom('C007');
        $salleC007->setEtage($etageRDC);
        $manager->persist($salleC007);
        $this->listSalles[] = $salleC007;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleC007);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD004 = new Salle();
        $salleD004->setNom('D004');
        $salleD004->setEtage($etageRDC);
        $manager->persist($salleD004);
        $this->listSalles[] = $salleD004;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD004);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD002 = new Salle();
        $salleD002->setNom('D002');
        $salleD002->setEtage($etageRDC);
        $manager->persist($salleD002);
        $this->listSalles[] = $salleD002;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD002);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD001 = new Salle();
        $salleD001->setNom('D001');
        $salleD001->setEtage($etageRDC);
        $manager->persist($salleD001);
        $this->listSalles[] = $salleD001;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD001);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleSecretariat = new Salle();
        $salleSecretariat->setNom('Secrétariat');
        $salleSecretariat->setEtage($etage1);
        $manager->persist($salleSecretariat);
        $this->listSalles[] =  $salleSecretariat;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleSecretariat);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD109 = new Salle();
        $salleD109->setNom('D109');
        $salleD109->setEtage($etage1);
        $manager->persist($salleD109);
        $this->listSalles[] =  $salleD109;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD109);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleC101 = new Salle();
        $salleC101->setNom('C101');
        $salleC101->setEtage($etage1);
        $manager->persist($salleC101);
        $this->listSalles[] =  $salleC101;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleC101);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD304 = new Salle();
        $salleD304->setNom('D304');
        $salleD304->setEtage($etage3);
        $manager->persist($salleD304);
        $this->listSalles[] = $salleD304;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD304);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD303 = new Salle();
        $salleD303->setNom('D303');
        $salleD303->setEtage($etage3);
        $manager->persist($salleD303);
        $this->listSalles[] = $salleD303;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD303);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD203 = new Salle();
        $salleD203->setNom('D203');
        $salleD203->setEtage($etage2);
        $manager->persist($salleD203);
        $this->listSalles[] = $salleD203;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD203);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD204 = new Salle();
        $salleD204->setNom('D204');
        $salleD204->setEtage($etage2);
        $manager->persist($salleD204);
        $this->listSalles[] = $salleD204;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD204);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD207 = new Salle();
        $salleD207->setNom('D207');
        $salleD207->setEtage($etage2);
        $manager->persist($salleD207);
        $this->listSalles[] = $salleD207;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD207);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD206 = new Salle();
        $salleD206->setNom('D206');
        $salleD206->setEtage($etage2);
        $manager->persist($salleD206);
        $this->listSalles[] = $salleD206;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD206);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);

        //-------------------------------------------------------------------------------------

        $salleD205 = new Salle();
        $salleD205->setNom('D205');
        $salleD205->setEtage($etage2);
        $manager->persist($salleD205);
        $this->listSalles[] = $salleD205;

        // Détails de la salle
        $detailsSalle = new DetailsSalle();
        $detailsSalle->setSalle($salleD205);
        $detailsSalle->setDateDeCreation($this->getRandomDate('2025-01-07'));
        $detailsSalle->setExposition($exposition[array_rand($exposition)]);
        $detailsSalle->setPorte(rand(1,2));
        $detailsSalle->setFenetre(rand(1,5));
        $detailsSalle->setRadiateur(rand(1,5));
        $detailsSalle->setFrequentation($frequentation[array_rand($frequentation)]);
        $detailsSalle->setSuperficie(rand(5,30));
        $manager->persist($detailsSalle);


        //-------------------------------------------------------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[0]);
        $plan->setSalle($salleD205);
        $manager->persist($plan);



        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[1]);
        $plan->setSalle($salleD206);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[2]);
        $plan->setSalle($salleD207);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[3]);
        $plan->setSalle($salleD204);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[4]);
        $plan->setSalle($salleD203);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[5]);
        $plan->setSalle($salleD303);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[6]);
        $plan->setSalle($salleD304);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[7]);
        $plan->setSalle($salleC101);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[8]);
        $plan->setSalle($salleD109);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[9]);
        $plan->setSalle($salleSecretariat);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[10]);
        $plan->setSalle($salleD001);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[11]);
        $plan->setSalle($salleD002);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[12]);
        $plan->setSalle($salleD004);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[13]);
        $plan->setSalle($salleC004);
        $manager->persist($plan);

        //-----------------------------------------------

        $plan = new Plan();
        $plan->setDateAssociation(new \DateTime());
        $plan->setSA($this->listSA[14]);
        $plan->setSalle($salleC007);
        $manager->persist($plan);

    }

    /**
     * @author Axel
     * @brief Génère une date aléatoire et la renvoie
     * @param string $dateMin le paramètre minimum de la création de la date
     * @return \DateTime la date random
     */
    function getRandomDate(string $dateMin): \DateTime
    {
        // Définit les maximum des dates
        $start = strtotime($dateMin); // Convertie date de début en timestamp
        $end = (new \DateTime())->getTimestamp(); // timestamp actuelle en int

        // Pour la date random:
        $randomTimestamp = mt_rand($start, $end); // Crée un timeStamp pour la date
        return (new \DateTime())->setTimestamp($randomTimestamp);
    }

    /**
     * @author Axel
     * @brief Génère 4 capteurs pour le SA
     * @param SystemAcquisition $sa le SA
     * @param ObjectManager $manager pour persist les données
     * @return void
     */
    function generateSensors(SystemAcquisition $sa, ObjectManager $manager): void
    {
        // récupère la date de création du SA pour ne pas créer de valeurs plus vieille
        $dateCreationSA = $sa->getDateCreation();
        $formatedDate = $dateCreationSA->format('Y-m-d H:i:s'); // Convertie en string
        $dateValue = $this->getRandomDate($formatedDate); // Pour avoir une date random

        // CO2
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::co2);
        $capteur->setValeur(rand(400, 2000));
        $capteur->setDate($dateValue);
        $manager->persist($capteur);

        // Température
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::temperature);
        $capteur->setValeur(rand(0,40));
        $capteur->setDate($dateValue);
        $manager->persist($capteur);

        // Humidité
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::humidite);
        $capteur->setValeur(rand(10,100));
        $capteur->setDate($dateValue);
        $manager->persist($capteur);

        // Luminosité
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::luminosite);
        $capteur->setValeur(rand(0,1));
        $capteur->setDate($dateValue);
        $manager->persist($capteur);
    }

    /**
     * @author Corentin
     * @brief Génère 4 capteurs pour les SA sans données brut
     * @param SystemAcquisition $sa le SA
     * @param ObjectManager $manager pour persist les données
     * @return void
     */
    function generateSensorsInfo(SystemAcquisition $sa, ObjectManager $manager): void
    {
        // récupère la date de création du SA pour ne pas créer de valeurs plus vieille
        $dateCreationSA = $sa->getDateCreation();
        $formatedDate = $dateCreationSA->format('Y-m-d H:i:s'); // Convertie en string
        $dateValue = $this->getRandomDate($formatedDate); // Pour avoir une date random

        // CO2
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::co2);
        $manager->persist($capteur);

        // Température
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::temperature);
        $manager->persist($capteur);

        // Humidité
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::humidite);
        $manager->persist($capteur);

        // Luminosité
        $capteur = new Capteur();
        $capteur->setSA($sa);
        $capteur->setType(Type::luminosite);
        $manager->persist($capteur);
    }

    /**
     * @author Axel
     * @brief Génère le plan en fonction de la liste des salles
     * @param ObjectManager $manager pour persist les plans
     */
    function generatePlan(ObjectManager $manager): void
    {
        // Génére des plans en fonction du nombre de SA par bâtiments
        for($i = 0; $i <= count($this->listSalles)/2; $i++)
        {
            $salleIndex = array_rand($this->listSalles);
            $salle = $this->listSalles[$salleIndex];
            unset($this->listSalles[$salleIndex]);

            $saIndex = count($this->listSA) -1;
            $SA = $this->listSA[$saIndex];
            unset($this->listSA[$saIndex]);

            $plan = new Plan();
            $plan->setDateAssociation(new \DateTime());
            $plan->setSA($SA);
            $plan->setSalle($salle);
            $manager->persist($plan);
        }

        // Génére des plans avec 2 SA en fonction du nombre de SA par bâtiments
        for($i = 0; $i <= count($this->listSalles)/10; $i++)
        {
            $salleIndex = array_rand($this->listSalles);
            $salle = $this->listSalles[$salleIndex];
            unset($this->listSalles[$salleIndex]);

            $saIndex = count($this->listSA) -1;
            $SA1 = $this->listSA[$saIndex];
            unset($this->listSA[$saIndex]);

            $saIndex1 = count($this->listSA) -1;
            $SA2 = $this->listSA[$saIndex1];
            unset($this->listSA[$saIndex1]);

            $plan = new Plan();
            $plan->setDateAssociation(new \DateTime());
            $plan->setSA($SA1);
            $plan->setSalle($salle);
            $manager->persist($plan);

            $plan = new Plan();
            $plan->setDateAssociation(new \DateTime());
            $plan->setSA($SA2);
            $plan->setSalle($salle);
            $manager->persist($plan);
        }

    }


    /**
     * @author Julien
     * @brief Génère les utilisateurs à partir de la liste en variable globale.
     * @param ObjectManager $manager Manager utilisé pour persist les données
     */
    function generateUsers(ObjectManager $manager): void
    {
        for ($i = 0; $i < count(logins); $i++)
        {
            $user = new Utilisateur();
            $user->setUsername(logins[$i][0]);

            $hashedPassword = $this->passwordHasher->hashPassword($user, logins[$i][1]);
            $user->setPassword($hashedPassword);

            $user->setNom(logins[$i][2]);

            $user->setRoles(logins[$i][3]);

            $manager->persist($user);
        }
    }

    /**
     * @autor Axel
     * @brief récupérer la météo et l'insère en base pour les triggers
     * @return void
     */
    function getWeather(KernelInterface $kernel): void
    {
        /** Récupère la météo quand on arrive sur la page bâtiment */
        try
        {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:fetch-weather',
            ]);

            // You can use NullOutput() if you don't need the output
            $output = new BufferedOutput();
            $application->run($input, $output);
        }
        catch (\Exception $e)
        {}
    }
}
