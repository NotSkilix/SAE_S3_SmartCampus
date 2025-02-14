<?php

namespace App\Controller;

use App\Entity\Type;
use App\Repository\ConseilRepository;
use App\Repository\MeteoRepository;
use App\Repository\SalleRepository;
use App\Repository\SystemAcquisitionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InfoSalleController extends AbstractController
{
    #[Route('/info/salle/{nomBatiment}/{nomSalle}', name: 'app_info_salle')]
    public function index(string $nomBatiment, string $nomSalle, SalleRepository $salleRepository, SystemAcquisitionRepository $systemAcquisitionRepository, MeteoRepository $meteoRepository, ConseilRepository $conseilRepository): Response
    {
        $salleId = $salleRepository->findIdByName($nomSalle, $nomBatiment);

        if (!$salleId) {
            throw $this->createNotFoundException("Salle ou bâtiment non trouvé.");
        }

        $salle = $salleRepository->find($salleId);
        $detailSalle = $salle->getDetailsSalle();

        $detailSalleSuperficie = $detailSalle->getSuperficie();
        $detailSalleExposition = $detailSalle->getExposition();
        $expositionValue = $detailSalleExposition?->value ?? 'Non défini';
        $detailSalleFrequentation = $detailSalle->getFrequentation();
        $frequentationValue = $detailSalleFrequentation?->value ?? 'Non défini';
        $detailSalleRadiateur  = $detailSalle->getRadiateur();
        $detailSalleFenetre = $detailSalle->getFenetre();
        $detailSallePorte = $detailSalle->getPorte();

        $SAs = $systemAcquisitionRepository->findByPlanSalle($salleId);

        // Initialiser les variables pour les capteurs
        $capteurTemp = 'Non défini';
        $capteurC02 = 'Non défini';
        $capteurHum = 'Non défini';
        $capteurLum = 'Non défini';
        $date = 'Non défini';

        foreach ($SAs as $SA) {
            $capteurs = $SA->getCapteurs();

            foreach ($capteurs as $capteur) {
                $capteurType = $capteur->getType();

                // Traitement des capteurs selon leur type
                if ($capteurType === Type::temperature) {
                    $capteurTemp = $capteur->getValeur() . ' °C';
                    $date = $capteur->getDate();
                }
                else if ($capteurType === Type::co2) {
                    $capteurC02 = $capteur->getValeur() . ' ppm';
                }
                else if ($capteurType === Type::humidite) {
                    $capteurHum = $capteur->getValeur() . ' %';
                }
                else if ($capteurType === Type::luminosite) {
                    $capteurLum = $capteur->getValeur();
                    if ($capteurLum) {
                        if ($capteurLum == 1) {
                            $capteurLum = "Oui";
                        } else {
                            $capteurLum = "Non";
                        }
                    } else {
                        $capteurLum = "?";
                    }

                }
            }
        }

        // Récupère la première (et normalement seule) ligne dans la table 'meteo'
        $actualWeather = $meteoRepository->findAll()[0];

        // Récupère la liste des conseils associés à la salle
        $conseils = $conseilRepository->getAllBySalle($salleId);

        return $this->render('info_salle/index.html.twig', [
            'batimentNom' => $nomBatiment,
            'idSalle' => $salleId,
            'nomSalle' => $nomSalle,
            'detailSalleSuperficie' => $detailSalleSuperficie,
            'detailSalleExposition' => $expositionValue,
            'detailSalleFrequentation' => $frequentationValue,
            'detailSalleRadiateur' => $detailSalleRadiateur,
            'detailSalleFenetre' => $detailSalleFenetre,
            'detailSallePorte' => $detailSallePorte,
            'capteurTemp' => $capteurTemp,
            'capteurC02' => $capteurC02,
            'capteurHum' => $capteurHum,
            'capteurLum' => $capteurLum,
            'date' => $date,
            'meteo' => $actualWeather,
            'conseils' => $conseils,
        ]);
    }


}
