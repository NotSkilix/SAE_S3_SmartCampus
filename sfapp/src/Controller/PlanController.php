<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\TypeNote;
use App\Form\AddNoteType;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Config\Doctrine\Dbal\ConnectionConfig\ReplicaConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Etat;
use App\Entity\Plan;
use App\Repository\PlanRepository;
use App\Repository\SystemAcquisitionRepository;
use App\Repository\SalleRepository;
use App\Form\PlanType;
use App\Form\ChangeEtatType;
use function PHPUnit\Framework\throwException;

class PlanController extends AbstractController
{
    // ROUTE PRINCIPALE
    // route pour afficher la liste des salles dans le plan
    #[Route('/plan', name: 'app_plan')]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function plan(PlanRepository $planRepository, SystemAcquisitionRepository $systemAcquisitionRepository, SalleRepository $salleRepository, Request $request): Response
    {
        //état des capteurs pour le tri
        $etats = Etat::cases();

        // sauvegarde l'url de la page
        $url = $request->getUri();
        $session = $request->getSession();
        $session->set('previous_url', $url);

        return $this->render('plan/index.html.twig', [
            'etats' => $etats,
        ]);
    }

    // ROUTE PRINCIPALE
    // route pour ajouter une nouvelle association
    #[Route('/plan/nouveau/{idSalle}', name: 'app_plan_nouveau')]
    #[IsGranted("ROLE_CHARGEMISSION", message: 'Vous n\'avez pas les autorisations requises pour accéder à cette page.')]
    public function nouveau(int $idSalle, Request $request, EntityManagerInterface $manager, PlanRepository $planRepository, SalleRepository $salleRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');
        $plan = new Plan();

        // idSalle = -1 quand on crée une association sans salle précise
        // sinon idSalle = l'id de la salle qu'on a choisi
        if($idSalle != -1)
        {
            // on récupère l'id de la salle et on l'associe au plan
            $salle = $salleRepository->findOneBy(['id' => $idSalle]);
            $plan->setSalle($salle);
        }
        else
        {
            $salle = $salleRepository->findOneBy(['id' => $idSalle]);
        }

        $form = $this->createForm(PlanType::class, $plan, [
            'v_batiment' => $batimentNom,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // push dans la base de donnée
            $plan->setDateAssociation(new \DateTime);

            $manager->persist($plan);
                
            $manager->flush();

            $this->addFLash('message', "Le SA a bien été associé à la salle");

            if($idSalle == -1)
            {
                //redirige vers le plan
                return $this->redirectToRoute('app_plan');
            }
            else
            {
                // redirige vers la page précédente
                $session = $request->getSession();
                $url = $session->get('previous_url');
                return $this->redirect($url);
            }
        }

        return $this->render('plan/new.html.twig', [
            'form' => $form,
            'salle' => $salle
        ]);
    }

    // route pour visualiser en détail et modifier un plan
    #[Route('/plan/modifier/{idSalle}', name: 'app_plan_modifier')]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function modifier(int $idSalle, SalleRepository $salleRepository, PlanRepository $planRepository, NoteRepository $noteRepository,SystemAcquisitionRepository $systemAcquisitionRepository, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');
        $salle = $salleRepository->findOneBy(['id' => $idSalle]);

        // liste des sa de la salle
        $plans = $planRepository->findBySalle($idSalle, $batimentNom);

        // pour changer l'état des sa
        $etats = Etat::cases();

        //création et validation des formulaires pour chaque sa
        $forms = [];
        foreach($plans as $plan)
        {
            $sa = $systemAcquisitionRepository->findByName($plan["nom"]);

            // création du formulaire
            $form = $this->createForm(ChangeEtatType::class, $sa);
            $form->handleRequest($request);
            //on récupère le nom du sa qui a été modifié
            $nomSA = $form->get('isSubmit')->getData();

            if ($form->isSubmitted() && $form->isValid() && $sa->getNom() == $nomSA) 
            {
                // redirection vers la route de validation de modification
                return $this->redirectToRoute('app_plan_modification', [
                    'idSalle' => $idSalle,
                    'nomSA' => $nomSA,
                    'etat' => $sa->getEtat()->value,
                ]);
            }

            // on stock les forms dans un tableau pour les afficher
            $forms[$sa->getNom()] = $form->createView();
        }

        $Note = new Note();

        $form = $this->createForm(AddNoteType::class, $Note, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $Note->setType(TypeNote::Information);
            $Note->setSalle($salle);
            $Note->setDate(new \DateTime);
            $manager->persist($Note);
            $manager->flush();

            // Recrée un formulaire vierge
            $note = new Note();
            $form = $this->createForm(AddNoteType::class, $note, []);

            $this->addFlash('message', 'Note ajoutée avec succès!');

            return $this->redirectToRoute('app_plan_modifier',[
                    'idSalle' => $idSalle,
            ]);

        }

        $listNote= $noteRepository->findBySalle($batimentNom, $idSalle);
        $typeNote = TypeNote::cases();

        // sauvegarde l'url de la page
        $url = $request->getUri();
        $session = $request->getSession();
        $session->set('previous_url', $url);

        return $this->render('plan/edit.html.twig', [
            'salle' => $salle,
            'plans' => $plans,
            'etats' => $etats,
            'forms' => $forms,
            'form' => $form,
            'listNote' => $listNote,
            'typeNote' => $typeNote,
        ]);
    }

    #[Route(path: '/plan/problemelu/{idProbleme}', name: 'app_plan_probleme')]
    public function probleme(int $idProbleme, EntityManagerInterface $manager, NoteRepository $noteRepository): Response
    {
        $note= $noteRepository->findOneById($idProbleme);

        $note->setType(TypeNote::ProblemeLu);
        $note->setDate(new \DateTime());
        $manager->persist($note);
        $manager->flush();

        return $this->redirectToRoute('app_plan_modifier', [
            'idSalle' => $note->getSalle()->getId(),
        ]);
    }

    /**
     * @author Côme, Axel
     * @brief change le type de la note pour "lu" suivant son type d'origine (normale ou batiment)
     * @param int $idProbleme l'id de la note
     * @param EntityManagerInterface $manager le manager pour persist et flush les données
     * @param NoteRepository $noteRepository le repository pour récupérer l'entité note
     * @return Response
     */
    #[Route(path: '/plan/problemelu/dashboard/{idProbleme}', name: 'app_plan_probleme_dashboard')]
    public function probleme_dashboard(int $idProbleme, EntityManagerInterface $manager, NoteRepository $noteRepository): Response
    {
        $note = $noteRepository->findOneById($idProbleme); // récupère la note

        switch($note->getType())
        {
            case TypeNote::Probleme:
                $note->setType(TypeNote::ProblemeLu);
                break;
            case TypeNote::ProblemeBatiment:
                $note->setType(TypeNote::ProblemeBatimentLu);
                break;
            default:
                $this->addFlash("message", "Erreur lors de la modification du problème");
                break;
        }


        $note->setDate(new \DateTime());
        $manager->persist($note);
        $manager->flush();

        $this->addFlash("message", "Problème lue");

        return $this->redirectToRoute('app_dashboard', []);
    }

     // route valider la modification d'un état
    #[Route('/plan/modifier/{idSalle}/{nomSA}/{etat}', name: 'app_plan_modification')]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function modification(int $idSalle, string $nomSA, Etat $etat, SystemAcquisitionRepository $systemAcquisitionRepository, EntityManagerInterface $manager): Response
    {
        // on recup le sa à partir du nom
        $sa = $systemAcquisitionRepository->findByName($nomSA);
        // on lui attribue l'état
        $sa->setEtat($etat);

        $manager->persist($sa);
        $manager->flush();

        // on revient sur la page précédente
        return $this->redirectToRoute('app_plan_modifier', [
            'idSalle' => $idSalle,
        ]);
    }

    // route de l'historique des plans
    #[Route('/plan/historique/{nom}', name: 'app_plan_historique', defaults: ['nom' => 'none'])]
    #[IsGranted("ROLE_TECHNICIEN", message: 'Vous devez être connecté pour avoir accès à cette page.')]
    public function historique(string $nom, SessionInterface $session, PlanRepository $planRepository): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');
        if($nom == "none")
            $plans = $planRepository->findAllByBatiment($batimentNom);
        else
            $plans = $planRepository->findBySAOrSalleName($nom, $batimentNom);

        return $this->render('plan/historique.html.twig', [
                'plans' => $plans,
        ]);
    }

    // =================================== REQUETE AJAX ===================================

    // route permettant de supprimer une association entre un sa et une salle
    #[Route('request/plan/supprimer/{saNom}', name: 'plan_supprimer', methods: ['DELETE'])]
    public function deleteSalle(string $saNom, PlanRepository $planRepository, SystemAcquisitionRepository $systemAcquisitionRepository, EntityManagerInterface $manager, Request $request): Response
    {
        // on recupère le plan associé au nom du sa
        $sa = $systemAcquisitionRepository->findByName($saNom);
        if ($sa->getEtat() == Etat::EnStock)
        {
            $plan = $planRepository->findBySA($sa->getId());
            // on lui attribue une date de fin d'association
            $plan->setDateDesassociation(new \DateTime);

            $manager->persist($sa);
            $manager->persist($plan);
            $manager->flush();

            $response = "Le SA a bien été enlevé de la salle";
            $this->addFLash('message', "Le SA a bien été enlevé de la salle");
        }
        elseif ($sa->getEtat() == Etat::AInstaller)
        {
            $sa->setEtat(Etat::EnStock);
            $plan = $planRepository->findBySA($sa->getId());
            // on lui attribue une date de fin d'association
            $plan->setDateDesassociation(new \DateTime);

            $manager->persist($sa);
            $manager->persist($plan);
            $manager->flush();

            $response = "Le SA a bien été enlevé de la salle";
            $this->addFLash('message', "Le SA a bien été enlevé de la salle");
        }
        else
        {
            $response = "Le SA est toujours installé";
            $this->addFLash('message', "Le SA est toujours installé");
        }


        return new JsonResponse($response);
    }

    // Route pour récupérer la liste des salles en fonction d'un état séléctionné
    #[Route('request/plan/findByEtat/{etat}', name: 'app_salle_findByEtat', methods: ['GET'])]
    public function findByEtat(SalleRepository $salleRepository, string $etat, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        if($etat === "tout")
            $salles = $salleRepository->findAllWithPlan($batimentNom);
        else
            $salles = $salleRepository->findByEtat($etat, $batimentNom);

        return new JsonResponse($salles);
    }

    // Route pour récupérer la liste des notes en fonction d'un type séléctionné
    #[Route('request/plan/findByType/{type}/{salle}', name: 'app_note_findByType', methods: ['GET'])]
    public function findByType(NoteRepository $noteRepository, string $type, string $salle, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        $Idsalle = intval($salle);

        if($type === "tout")
            $notes = $noteRepository->findBySalle($batimentNom, $Idsalle);
        else
            $notes = $noteRepository->findByType($type, $batimentNom, $Idsalle);

        $notes = array_map(function ($note) {
            return [
                'id' => $note->getId(),
                'type' => $note->getType(),
                'texte' => $note->getTexte(),
                'titre' => $note->getTitre(),
                'date' => $note->getDate()->format('Y/m/d H:i'),
            ];
        }, $notes);
        return new JsonResponse($notes);
    }

    // Route pour récupérer les sa d'une salle
    #[Route('request/plan/findBySalle/{idSalle}', name: 'app_salle_findBySalle', methods: ['GET'])]
    public function findBySalle(PlanRepository $planRepository, int $idSalle): Response
    {
        $plans = $planRepository->findBySalle($idSalle);

        return new JsonResponse($plans);
    }

    // Route pour récupérer les salles en fonction du nom de la salle ou d'un sa
    #[Route('request/plan/findBySalleOrSA/{nom}', name: 'app_salle_findBySearch', methods: ['GET'])]
    public function findBySearch(SalleRepository $salleRepository, PlanRepository $planRepository, string $nom,SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        $salles = $salleRepository->findBySearch($nom, $batimentNom);

        return new JsonResponse($salles);
    }
}
