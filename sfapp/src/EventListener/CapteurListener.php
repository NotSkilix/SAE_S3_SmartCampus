<?php

namespace App\EventListener;

use App\Entity\Capteur;
use App\Entity\Conseil;
use App\Entity\Meteo;
use App\Entity\Salle;
use App\Entity\Type;
use App\Entity\TypeConseil;
use App\Repository\CapteurRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;

// Les valeurs maximales à ne pas dépasser de chaque capteur
const MAX_CO2 = 1500;
const MAX_HUM = 70;
const MAX_TEMP_SUMMER = 28;
const MAX_TEMP_WINTER = 21;
const MIN_TEMP = 17;

// Période de la phase hivernale (mois)
const BEGIN_WINTER_PHASE = 10; // Octobre
const END_WINTER_PHASE = 04; // Avril


final class CapteurListener
{
    public function postPersist(PostPersistEventArgs $event): void
    {
        // récupére l'entityManagerInterface de l'objet
        $manager = $event->getObjectManager();

        // Le capteur qui a été persist
        $capteur = $event->getObject();

        // Récupére la météo
        $meteoRepository = $manager->getRepository(Meteo::class);
        $meteo = $meteoRepository->findAll();

        // Valide que l'entité est bien un capteur
        if (!$capteur instanceof Capteur)
        {
            return;
        }
        elseif ($capteur->getValeur() == null) // Si la valeur est null (pour les salles infos)
        {
            return;
        }


        /** En fonction du capteur on vérifie ça dépasse les limites */
        // CO2
        if($capteur->getType() == Type::co2)
        {
            if($capteur->getValeur() > MAX_CO2)
            {
                $conseilText = "Ouvrir les fenêtres et la porte pour faire un courant d'air";
                $description = "PPM Trop élevé dans la salle";
                $this->createConseil(TypeConseil::co2, $conseilText, $description, $capteur->getValeur(), $capteur->getId(), $manager);
            }
        }
        // Humidité
        if ($capteur->getType() == Type::humidite)
        {
            if ($meteo == null)
            {
                // Pas d'entité météo alors on ne peut pas comparer
                return;
            }
            else
            {
                $meteo = $meteo[0];
            }

            // Si la valeur dépasse bien le maximum et que l'humidité extérieure n'est pas supérieure à celle intérieure
            if($capteur->getValeur() > MAX_HUM && $capteur->getValeur() < $meteo->getHum())
            {
                $conseilText = "Ouvrir les fenêtres et la porte pour faire un courant d'air";
                $description = "Humidité trop élevé dans la salle";
                $this->createConseil(TypeConseil::hum, $conseilText, $description, $capteur->getValeur(), $capteur->getId(), $manager);
            }
            elseif ($capteur->getValeur() > MAX_HUM) // Lorsque l'extérieur est plus humide que l'intérieur
            {
                $conseilText = "Ouvrir la ou les portes pour faire un courant d'air";
                $description = "Humidité trop élevé dans la salle";
                $this->createConseil(TypeConseil::hum, $conseilText, $description, $capteur->getValeur(), $capteur->getId(), $manager);
            }
        }

        // Température
        if($capteur->getType() == Type::temperature)
        {
            if ($meteo == null)
            {
                // Pas d'entité météo alors on ne peut pas comparer
                return;
            }
            else
            {
                $meteo = $meteo[0];
            }

            // vérifie dans quelle saison nous sommes pour changer la température maximale
            $maxTemp = MAX_TEMP_SUMMER; // Par défaut à la phase estivale
            $currentDate = new \DateTime();
            $currentMonth = (int)$currentDate->format('m'); // récupere le mois et le met en int
            // Si la date actuelle est situé dans la phase hivernale
            if($currentMonth > BEGIN_WINTER_PHASE || $currentMonth < BEGIN_WINTER_PHASE);
            {
                $maxTemp = MAX_TEMP_WINTER;
            }

            // Si la valeur dépasse bien le maximum et que la température extérieure n'est pas supérieure à celle intérieure (refroidir la salle)
            if($capteur->getValeur() > $maxTemp && $capteur->getValeur() > $meteo->getTemp())
            {
                $conseilText = "Ouvrir les fenêtres et la porte pour faire un courant d'air";
                $description = "Température trop élevé";
                $this->createConseil(TypeConseil::temp, $conseilText, $description, $capteur->getValeur(), $capteur->getId(), $manager);
            } // Si température extérieure plus élevée qu'intérieure (refroidir)
            elseif ($capteur->getValeur() > $maxTemp)
            {
                $conseilText = "Ouvrir la ou les portes pour faire un courant d'air";
                $description = "Température trop élevé";
                $this->createConseil(TypeConseil::temp, $conseilText, $description, $capteur->getValeur(), $capteur->getId(), $manager);
            } // Si la température est en dessous du minimum
            elseif ($capteur->getValeur() < MIN_TEMP)
            {
                $conseilText = "Allumer le chauffage si possible";
                $description = "Température trop basse";
                $this->createConseil(TypeConseil::temp, $conseilText, $description, $capteur->getValeur(), $capteur->getId(), $manager);

            }
        }
    }

    /**
     * @author Axel
     * @brief Crée un conseil suivant le type, son texte de conseil, sa description, la valeur etc
     * @param TypeConseil $type le type du conseil
     * @param string $text le texte du conseil
     * @param string $description la description du conseil
     * @param int $value la valeur du capteur qui dépasse les limites
     * @param int $capteurID ID du capteur pour avoir la salle
     * @param EntityManagerInterface $manager le manager pour récupérer les répository et flush
     */
    public function createConseil(TypeConseil $type, string $text, string $description, int $value, int $capteurID, EntityManagerInterface $manager) : void
    {
        // Récupère l'ID de la salle
        $capteurRepository = $manager->getRepository(Capteur::class);
        $salleID = $capteurRepository->getSalleIdByCapteurID($capteurID);

        if($salleID == null)
        {
            return;
        }

        $salleRepository = $manager->getRepository(Salle::class);
        $salle = $salleRepository->findOneBy(['id' => $salleID]);

        $conseil = new Conseil();
        $conseil->setType($type);
        $conseil->setText($text);
        $conseil->setDescription($description);
        $conseil->setSalle($salle);
        $conseil->setValeur($value);

        $manager->persist($conseil);
        $manager->flush();
    }
}
