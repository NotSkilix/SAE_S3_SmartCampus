<?php

namespace App\Controller;

use App\Entity\Etage;
use App\Entity\Salle;
use App\Entity\Type;
use App\Entity\Plan;
use App\Form\AddSalleType;
use App\Repository\BatimentRepository;
use App\Entity\DetailsSalle;
use App\Form\ParentType;
use App\Repository\CapteurRepository;
use App\Repository\ConseilRepository;
use App\Repository\DetailsSalleRepository;
use App\Repository\EtageRepository;
use App\Repository\SalleRepository;
use App\Repository\SystemAcquisitionRepository;
use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Config\Doctrine\Dbal\ConnectionConfig\ReplicaConfig;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(EtageRepository $etageRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        if (!$batimentNom) {
            throw $this->createNotFoundException('Aucun bâtiment sélectionné.');
        }

        $etages = $etageRepository->findAllByBatiment($batimentNom);

        return $this->render('salle/index.html.twig', [
            'etages' => $etages,
        ]);
    }

    #[Route('salle/nouveau', name: 'app_salle_addNewSalle')]
    public function addNewSalle(Request $request, EntityManagerInterface $manager /* TEMP */, SystemAcquisitionRepository $systemAcquisitionRepository, SalleRepository $salleRepository, PlanRepository $planRepository, SessionInterface $session) : Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        //création des nouvelles entités
        $salle = new Salle();
        $details = new DetailsSalle();
        //récupère les sa disponibles
        $availableSA = $systemAcquisitionRepository->getAllAvalailableSA();

        // Créer le formulaire parent qui contient les deux formulaires
        $form = $this->createForm(ParentType::class, null, [
            'v_salle' => $salle,
            'v_details' => $details,
            'v_batiment' => $batimentNom,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $manager->persist($salle);

            // vérifier que le nom de la salle n'est pas déjà utilisé
            $requestOnName = $salleRepository->findByName($batimentNom, $salle->getNom(), -1);

            if(!empty($requestOnName))
            {
                $this->addFLash('message', "Nom de salle déjà utilisé");
            }
            else 
            {
                $this->addFLash('message', "Salle ajoutée avec succès");

                $salle->setDetailsSalle($details);
                $details->setDateDeCreation(new \DateTime());
                
                $manager->flush();

                return $this->redirectToRoute('app_salle');
            }
        }
        return $this->render('salle/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/salle/modifier/{idSalle}', name: 'app_edit_salle')]
    public function editSalle(SystemAcquisitionRepository $systemAcquisitionRepository, SalleRepository $salleRepository, PlanRepository $planRepository, int $idSalle, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        // récupération des entités
        $salle = $salleRepository->find($idSalle);
        $details = $salle->getDetailsSalle();
        $plans = $planRepository->findBySalle($salle->getId());// plans déjà attribués à la salle
        // récupère les sa disponibles
        $availableSA = $systemAcquisitionRepository->getAllAvalailableSA();

        $form = $this->createForm(ParentType::class, null, [
            'v_salle' => $salle,
            'v_details' => $details,
            'v_batiment' => $batimentNom,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // vérifier que le nom de la salle n'est pas déjà utilisé
            $requestOnName = $salleRepository->findByName($batimentNom, $salle->getNom(), $salle->getId());

            if(!empty($requestOnName))
            {
                $this->addFLash('message', "Nom de salle déjà utilisé");
            }
            else 
            {
                $this->addFLash('message', "Salle modifiée avec succès");

                $manager->persist($salle);
                
                $manager->flush();

                return $this->redirectToRoute('app_salle');
            }
        }
        return $this->render('salle/edit.html.twig', [
            'form' => $form,
            'plans' => $plans,
            'idSalle' => $idSalle,
            'availableSA' => $availableSA,
        ]);
    }

    // retire un sa d'une salle lors de la modification
    #[Route('/salle/modifier/{idSalle}/retirerSA/{idSA}', name:'app_remove_sa')]
    public function retirerSA(EntityManagerInterface $manager, int $idSA, int $idSalle, PlanRepository $planRepository)
    {
        // on récupère le plan à partir du sa qu'on veut désassocier
        $plan = $planRepository->findBySA($idSA);

        // remplacer par changer la date de désassociation
        // (manque l'attribut dans l'entité plan)
        $manager->remove($plan);

        $manager->flush();

        return $this->redirectToRoute('app_edit_salle', ['idSalle' => $idSalle]);
    }

    //attribue un sa à la salle lors de la modification
    #[Route('/salle/assos/{idSalle}/ajouterSA/{idSA}', name:'app_add_sa')]
    public function ajouterSA(EntityManagerInterface $manager, int $idSalle, int $idSA, SalleRepository $salleRepository, SystemAcquisitionRepository $systemAcquisitionRepository)
    {
        $salle = $salleRepository->find($idSalle);
        $sa = $systemAcquisitionRepository->find($idSA);

        //création d'un nouveau plan pour ajouter un sa
        $plan = new Plan();
        $plan->setSalle($salle);
        $plan->setDateAssociation(new \DateTime);
        $plan->setSA($sa);
        $manager->persist($plan);
        $manager->flush();

        return $this->redirectToRoute('app_edit_salle', ['idSalle' => $idSalle]);
    }

    //association des sa à une salle donnée
    #[Route('/salle/assos/{idSalle}', name: 'app_assos_salle')]
    public function assosSalle(SystemAcquisitionRepository $systemAcquisitionRepository, SalleRepository $salleRepository, PlanRepository $planRepository, int $idSalle, Request $request, EntityManagerInterface $manager): Response
    {
        // récupération des entités
        $salle = $salleRepository->find($idSalle);
        $plans = $planRepository->findBySalle($salle->getId());// plans déjà attribués à la salle
        // récupère les sa disponibles
        $availableSA = $systemAcquisitionRepository->getAllAvalailableSA();

        return $this->render('salle/assos.html.twig', [
            'idSalle' => $idSalle,
            'plans' => $plans,
            'availableSA' => $availableSA,
        ]);
    }

    // =================================== REQUETE AJAX ===================================
    #[Route('request/salle/findByEtage/{idEtage}', name: 'app_salle_findByEtage', methods: ['GET'])]
    public function findByEtage(SalleRepository $salleRepository, int $idEtage): Response
    {
        $salles = $salleRepository->findByEtage($idEtage);

        return new JsonResponse( [
            'salles' => $salles,
        ]);
    }


    #[Route('request/salle/deleteSalle/{nomSalle}', name: 'supp_salle', methods: ['DELETE'])]
    public function deleteSalle(string $nomSalle, LoggerInterface $logger, EntityManagerInterface $manager, SalleRepository $salleRepository, SystemAcquisitionRepository $systemAcquisitionRepository): Response
    {

        $salle = $salleRepository->findOneBy(['nom' => $nomSalle]);
        $manager->remove($salle);
        $manager->flush();

        $response = "La salle a bien été supprimée";
        $this->addFLash('message', "La salle a bien été supprimée");

        return new JsonResponse($response);
    }

    #[Route('/request/salle/getEtat/{idSA}', name: 'app_salle_getEtat', methods: ['GET'])]
    public function getEtat(int $idSA, SystemAcquisitionRepository $systemAcquisitionRepository, CapteurRepository $capteurRepository): Response
    {
        $SAStatus = $systemAcquisitionRepository->getSAStatus($idSA);
        $capteurs = $capteurRepository->findAllStatusBySA($idSA);

        return new JsonResponse( [
            'saStatus' => $SAStatus,
            'capteurs' => $capteurs,
        ]);
    }

    /**
     * @author Axel
     * @param int $idSalle = Id de la salle
     * @param int $idSA = id du SAn peut être null
     * @param CapteurRepository $capteurRepository = pour les infos des capteurs
     * @param DetailsSalleRepository $detailsSalleRepository = pour les détails de la salle
     * @param SystemAcquisitionRepository $systemAcquisitionRepository = pour les infos du SA
     * @return Response
     */
    #[Route('/request/salle/getDetailsSalle/{idSalle}/{idSA}', name: 'app_salle_getDetailsSalle', methods: ['GET'])]
    public function getDetailsSalle(int $idSalle, string $idSA, CapteurRepository $capteurRepository, DetailsSalleRepository $detailsSalleRepository, SystemAcquisitionRepository $systemAcquisitionRepository): Response
    {
        $capteurs = null;
        $NomSA = null;

        // Si il y a un SA attribué à la Salle
        if($idSA != null && $idSA != "null")
        {
            $idSA = intval($idSA);
            $capteurs = $capteurRepository->findAllBySA($idSA);
            $NomSA = $systemAcquisitionRepository->getAllSANameBySalle($idSalle);
        }

        $details = $detailsSalleRepository->findAllByIdSalle($idSalle);

        return new JsonResponse( [
            'capteurs' => $capteurs, // Vide si pas de SA
            'detailsSalle' => $details,
            'NomSA' => $NomSA, // Vide si pas de SA
        ]);
    }

    /**
     * @author Axel
     * @brief récupère les salles en fonction de leurs nom, utilisé
     *        dans 'affichageSalle.js' avec la barre de recherche
     * @param string $salleName = nom de la salle
     * @return Response = la réponse de la querry
     */
    #[Route('/request/salle/getSallesFromName/{salleName}', name: 'app_salle_getSallesFromName', methods: ['GET'])]
    public function getSallesFromName(string $salleName, SalleRepository $salleRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        $salles = $salleRepository->findAllSalleByName($salleName, $batimentNom);

        return new JsonResponse( [
            'salles' => $salles,
        ]);
    }

    /**
     * @author Axel
     * @brief Récupère le ou les conseils d'une salle passé en paramètre dans la route
     * @param int $salleID le paramètre de la route
     * @param ConseilRepository $conseilRepository pour trouver le ou les conseils
     * @return Response
     */
    #[Route('/request/salle/getConseilSalle/{salleID}', name: 'app_salle_getConseilSalle', methods: ['GET'])]
    public function getConseilSalle(int $salleID, ConseilRepository $conseilRepository): Response
    {
        // Variables
        $conseils = "Aucun paramètre passé";

        // Si l'argument a bien été passé et que c'est pas un string
        if($salleID > 0)
        {
            $conseils = $conseilRepository->getAllBySalle($salleID);

            // Si on ne trouve pas de conseils
            if($conseils == null)
            {
                $conseils = "Aucun conseil trouvé dans cette salle";
            }
        }


        return new JsonResponse( [
            'conseils' => $conseils,
        ]);
    }
}
