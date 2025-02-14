<?php

namespace App\Controller;

use App\Entity\Capteur;
use App\Entity\Etat;
use App\Entity\SystemAcquisition;
use App\Entity\Type;
use App\Form\AddSAType;
use App\Repository\CapteurRepository;
use App\Repository\SalleRepository;
use App\Repository\SystemAcquisitionRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SystemeAcquisitionController extends AbstractController
{
    /** Route par défaut */
    #[Route('/systeme_acquisition', name: 'app_systeme_acquisition')]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function index(SystemAcquisitionRepository $systemAcquisitionRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        if (!$batimentNom) {
            throw $this->createNotFoundException('Aucun bâtiment sélectionné.');
        }

        $allSA = $systemAcquisitionRepository->findAllByBatiment($batimentNom);

        return $this->render('systeme_acquisition/index.html.twig', [
            'allSystemAcquisition' => $allSA,
            'etats' => Etat::cases(),
        ]);
    }


    /** Route pour l'ajout d'un SA **/
    #[Route('/systeme_acquisition/nouveau', name: 'app_ajout_sa')]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function ajoutSA(SystemAcquisitionRepository $systemAcquisitionRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $sa = new SystemAcquisition();

        $form = $this->createForm(AddSAType::class, $sa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si le nom est déjà utilisé
            $requestOnName = $systemAcquisitionRepository->findByName($sa->getNom());
            if (!empty($requestOnName))
            {
                $this->addFlash("message", "Ce nom est déjà utilisé par un autre système d'acquisition");
            }
            else
            {
                // Défini les attributs du SA
                $sa->setEtat(Etat::EnStock);
                $sa->setDateCreation(new \DateTime());
                $manager->persist($sa);

                // Crée un capteur de chaque type et le lie au SA
                foreach ([Type::temperature, Type::co2, Type::humidite, Type::luminosite]
                         as $capteurInfo)
                {
                    $capteur = new Capteur();
                    $capteur->setSA($sa);
                    $capteur->setType($capteurInfo);
                    $manager->persist($capteur);
                }
                $manager->flush();
                $this->addFlash("message", "Le système d'acquisition a bien été ajouté");

                // Redirige vers la liste des SA
                return $this->redirectToRoute('app_systeme_acquisition');
            }
            // Redirige vers la page d'ajout de SA (rafraichi la page pour afficher le popup)
            return $this->redirectToRoute('app_ajout_sa');
        }

        return $this->render('systeme_acquisition/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/systeme_acquisition/modifier/{idSA}', name: 'app_edit_systeme_acquisition')]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function editSA(SystemAcquisitionRepository $systemAcquisitionRepository, int $idSA, Request $request, EntityManagerInterface $manager): Response
    {
        $sa = $systemAcquisitionRepository->find($idSA);

        $form = $this->createForm(AddSAType::class, $sa);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $requestOnName = $systemAcquisitionRepository->findByName($sa->getNom(), $sa->getId());

            if (!empty($requestOnName))
            {
                $this->addFlash("message", "Ce nom est déjà utilisé par un autre système d'acquisition");
            }
            else
            {
                $this->addFLash('message', "Système d'acquisition modifié avec succès");
                $manager->persist($sa);
                $manager->flush();

                return $this->redirectToRoute('app_systeme_acquisition');
            }
        }

        return $this->render('systeme_acquisition/edit.html.twig', [
            'sa' => $sa,
            'form' => $form,
        ]);
    }

    // =================================== REQUÊTE AJAX ===================================
    /**  Route pour la requête Ajax*/
    #[Route('request/systeme_acquisition/getSensorsBySA/{idSA}', name: 'get_sensors_by_sa', methods: ['GET'])]
    public function getSensorsBySA(CapteurRepository $capteurRepository, int $idSA): Response
    {
        $sensors = $capteurRepository->findAllBySA($idSA);

        return new JsonResponse($sensors);
    }

    /** Requête AJAX pour obtenir les SA selon un état donné. */
    #[Route('request/systeme_acquisition/getSAByEtat/{etat}', name: 'get_sa_by_etat', methods: ['GET'])]
    public function getSAByEtat(SystemAcquisitionRepository $systemAcquisitionRepository, SessionInterface $session, string $etat): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        if (!$batimentNom) {
            throw $this->createNotFoundException('Aucun bâtiment sélectionné.');
        }

        if($etat == "tout") {
            $systemAcquisition = $systemAcquisitionRepository->findAllByBatiment($batimentNom);
        } else {
            $systemAcquisition = $systemAcquisitionRepository->findByEtat($etat, $batimentNom);
        }

        return new JsonResponse($systemAcquisition);
    }

    #[Route('request/systeme_acquisition/getSAByNameAndEtat', name: 'get_sa_by_name_and_etat', methods: ['GET'])]
    public function getSAByNameAndEtat(SystemAcquisitionRepository $systemAcquisitionRepository, SessionInterface $session): Response
    {
        $nom = $_GET['nom'];
        $etat = $_GET['etat'];
        $nomBatiment = $session->get('batiment_selectioné_nom');

        if (!$nomBatiment)
        {
            throw $this->createNotFoundException("Aucun bâtiment sélectionné.");
        }

        if ($nom == '')
        {
            if ($etat == "tout")
            {
                $sa = $systemAcquisitionRepository->findAllByBatiment($nomBatiment);
            }
            else
            {
                $sa = $systemAcquisitionRepository->findByEtat($etat, $nomBatiment);
            }
        }
        else
        {
            if ($etat == "tout")
            {
                $sa = $systemAcquisitionRepository->findAllByName($nom, $nomBatiment);
            }
            else
            {
                $sa = $systemAcquisitionRepository->findByNameAndEtat($nom, $etat, $nomBatiment);
            }
        }

        return new JsonResponse($sa);
    }

    #[Route('request/systeme_acquisition/getSalleBySA/{idSA}', name: 'get_salles_by_sa', methods: ['GET'])]
    public function getSalleBySA(int $idSA, SalleRepository $salleRepository): Response
    {
        $salle = $salleRepository->findBySystemAcquisitionId($idSA);

        return new JsonResponse($salle);
    }

    #[Route('request/systeme_acquisition/deleteSa/{id}/{isSalleAssosiated}', name: 'supp_sa', methods: ['DELETE'])]
    public function deleteSa(int $id, string $isSalleAssosiated,  SystemAcquisitionRepository $systemAcquisitionRepository, SalleRepository $salleRepository, EntityManagerInterface $manager): Response
    {
        $systemAcquisition = $systemAcquisitionRepository->find($id);

        $plan = $systemAcquisition->getPlan();

        if ($plan) {
            // TODO: À fix - la popup lorsque qu'un SA est associé à une salle ne s'affiche pas tout le temps
            $message = "Le système d'acquisition ainsi que son association à une salle ont bien été supprimés avec succès.";
            $plan->setDateDesassociation(new \DateTime());
            $manager->persist($plan);
            $manager->flush();
        } else {
            $message = "Le système d'acquisition a bien été supprimé avec succès.";
        }
        $manager->remove($systemAcquisition);
        $manager->flush();

        $this->addFLash('message', $message);
        $response = "Système d'acquisition supprimé avec succès, mais le plan reste avec la date de désassociation.";
        return new JsonResponse($response, Response::HTTP_OK);
    }

    /**
     * @author Axel
     * @brief récupère les SA avec le nom passé dans la barre de recherche
     * @param string $name = le texte posé dans la barre de recherche
     * @param SystemAcquisitionRepository $systemAcquisitionRepository = la ou se trouve la querry
     * @return Response = la réponse Json de la querry
     */
    #[Route('request/systeme_acquisition/getSAByName/{name}', name: 'get_SA_by_name', methods: ['GET'])]
    public function getSAByName(string $name, SystemAcquisitionRepository $systemAcquisitionRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');
        $SA = $systemAcquisitionRepository->findAllByName($name, $batimentNom);

        return new JsonResponse([
            'SA' => $SA,
        ]);
    }

    #[Route('request/systeme_acquisition/getAllSA', name: 'get_all_sa', methods: ['GET'])]
    public function getAllSA(SystemAcquisitionRepository $systemAcquisitionRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');
        $SA = $systemAcquisitionRepository->findAllByBatiment($batimentNom);

        return new JsonResponse([
            'SA' => $SA
        ]);
    }

    /**
     * @autor Côme
     * @brief enregistre pour chaque capteur de l'ESP leur nouvelle valeur
     * @param CapteurRepository $capteurRepository = permet de récuperer les cateurs d'un ESP
     * @param string $ESP = nom de l'ESP concerné
     * @param string $co2 = valeur du capteur de co2
     * @param string $hum = valeur du capteur d'humidité
     * @param string $temp = valeur du capteur de température
     * @param string $lum = valeur du capteur de lumière
     * @return Response
     */
    #[Route('request/systeme_acquisition/setData/{ESP}/{co2}/{hum}/{temp}/{lum}/{date}', name: 'set_data', methods: ['POST'])]
    public function setData(CapteurRepository $capteurRepository, EntityManagerInterface $manager, string $ESP, string $co2, string $hum, string $temp, string $lum, string $date, SalleRepository $salleRepository): Response
    {
        // Convertir les valeurs des capteurs en entiers
        $co2 = intval($co2);
        $hum = intval($hum);
        $temp = intval($temp);
        $lum = intval($lum);
        $format = "Y-m-d H:i:s"; // Format correspondant
        $date = DateTime::createFromFormat($format, $date);
        if($date) {
            $capteurC02 = $capteurRepository->findCapteur($ESP, Type::co2);
            $capteurC02[0]->setValeur($co2);
            $capteurC02[0]->setDate($date);
            $manager->persist($capteurC02[0]);

            $capteurHUM = $capteurRepository->findCapteur($ESP, Type::humidite);
            $capteurHUM[0]->setValeur($hum);
            $capteurHUM[0]->setDate($date);
            $manager->persist($capteurHUM[0]);

            $capteurTEMP = $capteurRepository->findCapteur($ESP, Type::temperature);
            $capteurTEMP[0]->setValeur($temp);
            $capteurTEMP[0]->setDate($date);
            $manager->persist($capteurTEMP[0]);

            $capteurLUM = $capteurRepository->findCapteur($ESP, Type::luminosite);
            $capteurLUM[0]->setValeur($lum);
            $capteurLUM[0]->setDate($date);
            $manager->persist($capteurLUM[0]);
        }
        else
        {
            $capteurC02 = $capteurRepository->findCapteur($ESP, Type::co2);
            $capteurC02[0]->setValeur($co2);
            $manager->persist($capteurC02[0]);

            $capteurHUM = $capteurRepository->findCapteur($ESP, Type::humidite);
            $capteurHUM[0]->setValeur($hum);
            $manager->persist($capteurHUM[0]);

            $capteurTEMP = $capteurRepository->findCapteur($ESP, Type::temperature);
            $capteurTEMP[0]->setValeur($temp);
            $manager->persist($capteurTEMP[0]);

            $capteurLUM = $capteurRepository->findCapteur($ESP, Type::luminosite);
            $capteurLUM[0]->setValeur($lum);
            $manager->persist($capteurLUM[0]);
        }

        $manager->flush();

        $response = "Le batiment a bien été selectionné";

        return new JsonResponse($date);
    }
}
