<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Etage;
use App\Form\AddBatimentType;
use App\Form\AddEtageType;
use App\Form\EditBatimentType;
use App\Form\EditEtageType;
use App\Repository\BatimentRepository;
use App\Repository\EtageRepository;
use App\Repository\PlanRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;


class AccueilController extends AbstractController
{
    /**
     * @author Côme, Axel
     * @brief Page index du site '/', affiche la liste des bâtiments et executer la commande pour
     *        peupler la base et l'entité météo
     * @param BatimentRepository $batimentRepository = pour la liste des batiment et si le nom existe déja
     * @param Request $request la requête du client
     * @param EntityManagerInterface $manager pour créer un batiment ou la météo si elle n'existe pas
     * @param KernelInterface $kernel le kernel pour executer la commande
     * @return Response
     */
    #[Route('/', name: 'app_accueil')]
    public function index(BatimentRepository $batimentRepository, Request $request, EntityManagerInterface $manager, KernelInterface $kernel): Response
    {
        $batiment = new Batiment();

        $form = $this->createForm(AddBatimentType::class, $batiment);
        $form->handleRequest($request);

        $sent=false;
        if ($form->isSubmitted() && $form->isValid())
        {
            // vérifier que le nom du batiment n'est pas déjà utilisé
            $requestOnName = $batimentRepository->findByName($batiment->getNom(), -1);

            if(!empty($requestOnName))
            {
                $this->addFLash('message', "Nom de batiment déjà utilisé");
            }
            else
            {
                $this->addFLash('message', "Batiment ajouter avec succès");

                $manager->persist($batiment);
                $manager->flush();

                $sent = true;

                return $this->redirectToRoute('app_accueil');
            }
        }

        $allBatiment = $batimentRepository->findAll();

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


        return $this->render('accueil/index.html.twig', [
            'allBatiment' => $allBatiment,
            'text' => $sent,
            'form' => $form,
        ]);
    }

    /**
     * @autor Côme
     * @param string $nomBatiment = permet de transmettre à tout le projet le nom du batiment selectionné
     * @return Response
     */
    #[Route('request/batiment_selectioné/{nomBatiment}', name: 'app_batiment_selectioné', methods: ['GET'])]
    public function BatimmentSelect(Request $request, SessionInterface $session, string $nomBatiment): Response
    {
        //transmet le nom du batiment à toute la session/projet
        $session->set('batiment_selectioné_nom', $nomBatiment);

        $response = "Le batiment a bien été selectionné";

        return new JsonResponse($response);
    }

    /**
     * @autor Côme
     * @param BatimentRepository $batimentRepository = permet de trouvé le batiment a supprimé
     * @param EtageRepository $etageRepository = permet de trouver tout les etages du batiment a supprimé
     * @param SalleRepository $salleRepository = permet de trouver toute les salles des etage a supprimé
     * @return Response
     */
    #[Route('request/batiment/deleteBatiment/{nomBatiment}', name: 'supp_batiment', methods: ['DELETE'])]
    public function deleteBatiment(SessionInterface $session, string $nomBatiment, LoggerInterface $logger, EntityManagerInterface $manager, BatimentRepository $batimentRepository, EtageRepository $etageRepository, SalleRepository $salleRepository): Response
    {
        //retire le nom du batiment de la valeur dans la session
        $session->set('batiment_selectioné_nom', null);

        //supprime toutes les salles et tous les étages du batiment
        $etage = $etageRepository->findAllByBatiment($nomBatiment);
        for ($i = 0; $i < count($etage); $i++) {
            $salle = $salleRepository->findAllInEtage($etage[$i]->getId());
            for ($j = 0; $j < count($salle); $j++) {
                $manager->remove($salle[$j]);
                $manager->flush();
            }
            $manager->remove($etage[$i]);
            $manager->flush();
        }

        $batiment = $batimentRepository->findOneBy(['nom' => $nomBatiment]);
        $manager->remove($batiment);
        $manager->flush();

        $response = "Le batiment a bien été supprimée";
        $this->addFLash('message', "Le batiment a bien été supprimée");

        return new JsonResponse($response);
    }

    /**
     * @autor Côme
     * @param BatimentRepository $batimentRepository = permet de récupérer les données du batiment a modifié
     * @param EtageRepository $etageRepository = permet de verifier si le nom est déja utiliser et de trouver tous les étages du batiment
     * @param int $idBatiment = id du batiment concerné par les modifications
     * @param int|null $idEtage = id de l'étage a modifié par default a nul
     * @return Response
     */
    #[Route('/accueil/modifier/{idBatiment}/{idEtage}', name: 'app_edit_batiment', defaults: ["idEtage" => null])]
    public function editBatiment(BatimentRepository $batimentRepository, EtageRepository $etageRepository,int $idBatiment, ?int $idEtage, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        // récupération des entités
        $batiment = $batimentRepository->find($idBatiment);

        //form pour la modification du nom du batiment
        $formBatiment = $this->createForm(EditBatimentType::class, null, [
            'v_batiment' => $batiment,
        ]);

        //form pour la creation d'un batiment
        $etage = new Etage();
        $formEtage = $this->createForm(AddEtageType::class, $etage);

        $formBatiment->handleRequest($request);
        $formEtage->handleRequest($request);

        if ($formBatiment->isSubmitted() && $formBatiment->isValid())
        {
            // vérifier que le nom du batiment n'est pas déjà utilisé
            $requestOnName = $batimentRepository->findByName($batiment->getNom(), $batiment->getId());

            if(!empty($requestOnName))
            {
                $this->addFLash('message', "Nom de batiment déjà utilisé");
            }
            else
            {
                $this->addFLash('message', "Batiment modifiée avec succès");

                $manager->persist($batiment);

                $manager->flush();

                //change la valeur pour le nouveau nom dans la session
                $session->set('batiment_selectioné_nom', $batiment->getNom());

                return $this->redirectToRoute('app_accueil');
            }
        }
        elseif($formEtage->isSubmitted() && $formEtage->isValid())
        {
            // vérifier que le nom de l'étage n'est pas déjà utilisé
            $requestOnName = $etageRepository->findByName($etage->getNomComplet(), -1, $idBatiment);

            if(!empty($requestOnName))
            {
                $this->addFlash('message', "Nom d'etage déjà utilisé");
            }
            else
            {
                $this->addFlash('message', "Etage ajouté au batiment");
                $etage->setBatiment($batiment);
                $manager->persist($etage);
                $manager->flush();

                return $this->redirectToRoute('app_edit_batiment', ['idBatiment' => $idBatiment]);
            }
        }
        //condition pour la modification d'un batiment
        elseif ($idEtage!=null)
        {
            //recherche de l'étage concerné
            $editEtage = $etageRepository->find($idEtage);

            //form de la modification de l'étage
            $formEditEtage = $this->createForm(EditEtageType::class, null, [
                'v_etage' => $editEtage,
            ]);

            $formEditEtage->handleRequest($request);

            if ($formEditEtage->isSubmitted() && $formEditEtage->isValid())
            {
                // vérifier que le nom de l'étage n'est pas déjà utilisé
                $requestOnName = $etageRepository->findByName($editEtage->getNomComplet(), $idEtage, $idBatiment);

                if(!empty($requestOnName))
                {
                    $this->addFLash('message', "Nom de l'étage déjà utilisé");
                }
                else
                {
                    $this->addFLash('message', "Etage modifiée avec succès");

                    $manager->persist($editEtage);

                    $manager->flush();

                    return $this->redirectToRoute('app_dashboard', ['idBatiment' => $idBatiment]);
                }
            }

            //recherche tous les étages du batiment concerné par la modification
            $etages = $etageRepository->findAllByBatimentId($idBatiment);

            //renvoie dans un tableau le nombre de salle de chaque étage
            $arrayOfNbSalle = [];
            foreach ($etages as $niveau)
            {
                $arrayOfNbSalle[] = ($etageRepository->CountSalleForOneEtage($niveau->getId()));
            }

            return $this->render('accueil/edit.html.twig', [
                'formBatiment' => $formBatiment,
                'formEtage' => $formEtage,
                'formEditEtage' => $formEditEtage,
                'etages' => $etages,
                'arrayOfNbSalle' => $arrayOfNbSalle,
                'idEtage' => $idEtage,
                'idBatiment' => $idBatiment,
            ]);
        }

        //recherche tous les étages du batiment concerné par la modification
        $etages = $etageRepository->findAllByBatimentId($idBatiment);

        //renvoie dans un tableau le nombre de salles de chaque étage
        $arrayOfNbSalle = [];
        foreach ($etages as $niveau)
        {
            $arrayOfNbSalle[] = ($etageRepository->CountSalleForOneEtage($niveau->getId()));
        }

        return $this->render('accueil/edit.html.twig', [
            'formBatiment' => $formBatiment,
            'formEtage' => $formEtage,
            'etages' => $etages,
            'arrayOfNbSalle' => $arrayOfNbSalle,
            'idEtage' => $idEtage,
            'idBatiment' => $idBatiment,
        ]);
    }

    /**
     * @author Axel
     * @brief Supprime l'étage selectionné grâce à son ID.
     *        Supprime toutes les dépendances.
     * @param int $idEtage = id de l'étage
     * @param EntityManagerInterface $manager = pour supprimer dans la base
     * @param EtageRepository $etageRepository = pour avoir l'étage
     * @param SalleRepository $salleRepository = pour avoir les salles de l'étage
     * @param PlanRepository $planRepository = pour avoir les plans des salles
     * @return Response = si réussi
     */
    #[Route('/request/etage/deleteEtage/{idEtage}', name: 'app_accueil_deleteEtage', methods: ['DELETE'])]
    public function deleteEtage(int $idEtage, EntityManagerInterface $manager, EtageRepository $etageRepository, SalleRepository $salleRepository, PlanRepository $planRepository): Response
    {
        $etage = $etageRepository->find($idEtage);


        // Supprime toutes les salles
        $salles = $salleRepository->findBy(['etage' => $etage]);
        foreach ($salles as $salle)
        {

            // Supprime tous les plans
            $plans = $planRepository->findBy(['salle' => $salle]);
            foreach ($plans as $plan)
            {
                $manager->remove($plan);
            }


            $manager->remove($salle);
        }
        $manager->remove($etage);


        $manager->flush();

        $this->addFLash('message', "L'étage a bien été supprimée");

        return new JsonResponse(['message' => "L'étage a bien été supprimé"]);
    }


    /**
     * @author Axel
     * @brief Récupère la liste des bâtiments en fonction du nom dans la barre de recherche
     * @param string $batimentName = le nom dans la barre de recherche
     * @param BatimentRepository $batimentRepository = pour la querry pour avoir les bâtiments
     * @return Response = la réponse de la querry
     */
    #[Route('request/accueil/getBatimentByName/{batimentName}', name: 'app_accueil_getBatimentByName', methods: ['GET'])]
    public function getBatimentByName(string $batimentName, BatimentRepository $batimentRepository): Response
    {
        $batiment = $batimentRepository->findAllBatimentByName($batimentName);

        return new JsonResponse(
            ['batiment' => $batiment]
        );
    }

    /**
     * @author Axel
     * @brief Récupère tout les bâtiments en base avec leurs détails
     * @param BatimentRepository $batimentRepository = pour la querry
     * @return Response = la réponse de la querry
     */
    #[Route('request/accueil/getAllBatimentsWithDetails', name:'app_accueil_getAllBatiments', methods: ['GET'])]
    public function getAllBatimentsWithDetails(BatimentRepository $batimentRepository): Response
    {
        $batiments = $batimentRepository->findAllWithDetails();

        return new JsonResponse([
            'batiments' => $batiments
        ]);
    }
}
