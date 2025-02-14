<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SalleRepository;
use App\Repository\BatimentRepository;
use App\Repository\SystemAcquisitionRepository;
use App\Repository\CapteurRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DiagnosticController extends AbstractController
{
    /**
     * @autor Victor
     * @brief Page princiaple des diagnostics, affiche les graphiques
     * @param string localisation : batiment ou salle séléctionné
     * @return string localisation : batiment ou salle séléctionné s'il existe et -1 si il n'existe nulle part
     */
    #[Route('/diagnostic/{localisation}', name: 'app_diagnostic')]
    public function index(string $localisation, SalleRepository $salleRepository, SessionInterface $session, SystemAcquisitionRepository $systemAcquisitionRepository, CapteurRepository $capteurRepository): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        $salles = $salleRepository->findAllWithPlan($batimentNom);

        if($localisation == "first")
        {
            $localisation = $salles[0]['nom'];
        }

        /* --- GRAPHIQUES DES CAPTEURS --- */
        $co2Value = null;
        $tempValue = null;
        $humValue = null;
        $loc = $salleRepository->findByName($batimentNom, $localisation, -1);
        if(empty($loc))
        {
            $localisation = ""; // renvoie none si la localisation recherchée n'existe pas
        }
        else
        {
            $sa = $systemAcquisitionRepository->findBySalleName($localisation, $batimentNom);
            if(empty($sa))
                $localisation = "";
            else
            {
                $capteurs = $capteurRepository->findAllBySA($sa->getId());
                $co2Value = $capteurs[0]["valeur"];
                $tempValue = $capteurs[1]["valeur"];
                $humValue = $capteurs[2]["valeur"];
            }
        }

        return $this->render('diagnostic/index.html.twig', [
            'localisation' => $localisation,
            'tempValue' => $tempValue,
            'humValue' => $humValue,
            'co2Value' => $co2Value,
            'salles' => $salles,
        ]);
    }
}
