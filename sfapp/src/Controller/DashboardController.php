<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\TypeNote;
use App\Form\EditTexteNoteType;
use App\Repository\CapteurRepository;
use App\Repository\NoteRepository;
use App\Repository\ConseilRepository;
use App\Repository\SalleRepository;
use App\Repository\SystemAcquisitionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends AbstractController
{

    /**
     * @author Corentin, Axel, Côme
     * @brief route index du dashboard
     * @param SystemAcquisitionRepository $systemAcquisitionRepository pour récupérer le nombres de SA a installé ou en demande
     *                                                                 d'intervention (Technicien uniquement pour l'instant)
     * @param NoteRepository $noteRepository pour avoir les notes de la page suivant le rôle
     * @param SessionInterface $session pour savoir le nom du bâtiment selectionné
     * @param ConseilRepository $conseilRepository pour récupérer les conseils (Utilisateur lambda uniquement pour l'instant)
     * @param EntityManagerInterface $manager permet de persist et flush une entité
     * @return Response
     */
    #[Route('/dashboard/{idNote}', name: 'app_dashboard', defaults: ["idNote" => null])]
    public function dashboard(Request $request, SystemAcquisitionRepository $systemAcquisitionRepository, NoteRepository $noteRepository, SessionInterface $session, ConseilRepository $conseilRepository, EntityManagerInterface $manager, ?int $idNote): Response
    {
        // Sélectionne le bâtiment
        $batimentNom = $session->get('batiment_selectioné_nom');


        /** Données pour le technicien */
        $systemAcquisitionsInstall = $systemAcquisitionRepository->countSAByEtat('À installer', $batimentNom);
        $systemAcquisitionsIntervention = $systemAcquisitionRepository->countSAByEtat('Intervention nécessaire', $batimentNom);
        $note = $noteRepository->findAllProbleme($batimentNom); // Les notes 'basiques'
        $noteBatiment = $noteRepository->findAllProblemeBatimentByType(TypeNote::ProblemeBatiment, $batimentNom);
        $displayInstall = $systemAcquisitionsInstall != 0;
        $displayIntervention = $systemAcquisitionsIntervention != 0;

        /** Données pour le chargé de mission */
        $listNoteNonEnvoye = $noteRepository->findAllProblemeBatimentByType(TypeNote::ProblemeBatimentNonEnvoye, $batimentNom); // Notes a envoyé au technicien
        $nbNotesNonEnvoye = count($listNoteNonEnvoye); // Nombres de notes

        /** Données pour l'utilisateur lambda*/
        $listConseil = $conseilRepository->getAllInBatimentWithSalle($batimentNom);

        if($idNote != null)
        {
            $editNote = $noteRepository->find($idNote);

            $form = $this->createForm(EditTexteNoteType::class, $editNote);


            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $this->addFLash('message', "Note modifiée avec succès");

                $manager->persist($editNote);

                $manager->flush();

                return $this->redirectToRoute('app_dashboard');
            }
            return $this->render('dashboard/index.html.twig', [
                'systemAcquisitionsInstall' => $systemAcquisitionsInstall,
                'systemAcquisitionsIntervention' => $systemAcquisitionsIntervention,
                'notes' => $note,
                'listConseil' => $listConseil,
                'displayInstall' => $displayInstall,
                'displayIntervention' => $displayIntervention,
                'listNoteNonEnvoye' => $listNoteNonEnvoye,
                'nbNotesNonEnvoye' => $nbNotesNonEnvoye,
                'noteBatiments' => $noteBatiment,
                'form' => $form,
                'idNote' => $idNote,
            ]);
        }

        return $this->render('dashboard/index.html.twig', [
            'systemAcquisitionsInstall' => $systemAcquisitionsInstall,
            'systemAcquisitionsIntervention' => $systemAcquisitionsIntervention,
            'notes' => $note,
            'listConseil' => $listConseil,
            'displayInstall' => $displayInstall,
            'displayIntervention' => $displayIntervention,
            'listNoteNonEnvoye' => $listNoteNonEnvoye,
            'nbNotesNonEnvoye' => $nbNotesNonEnvoye,
            'noteBatiments' => $noteBatiment,
            'idNote' => $idNote,
        ]);
    }

    /**
     * @author Julien
     * @brief Requête AJAX pour obtenir les valeurs des capteurs associés à une salle.
     * @param int $salleId Identifiant de la salle souhaitée
     * @param CapteurRepository $capteurRepository Repository des capteurs
     * @return Response Réponse JSON avec la liste des valeurs des capteurs
     */
    #[Route('/request/dashboard/getSensorsFromSalle/{salleId}', name: 'ajax_getSensorsFromSalle', methods: ['GET'])]
    public function getSensorsFromSalle(int $salleId, CapteurRepository $capteurRepository): Response
    {
        $sensors = $capteurRepository->findBySalle($salleId);

        return new JsonResponse($sensors);
    }

    /**
     * @author Julien
     * @brief Requête AJAX pour obtenir les salles associées au bâtiment sélectionné.
     * @param SalleRepository $salleRepository Repository des salles
     * @param SessionInterface $session Session actuelle
     * @return Response Réponse JSON avec la liste des salles du bâtiment
     */
    #[Route('/request/dashboard/getSallesFromActualBatiment', name: 'ajax_getSallesFromActualBatiment', methods: ['GET'])]
    public function getSallesFromActualBatiment(SalleRepository $salleRepository, SessionInterface $session): Response
    {
        $batimentNom = $session->get('batiment_selectioné_nom');

        if (!$batimentNom) {
            throw $this->createNotFoundException('Aucun bâtiment sélectionné.');
        }

        $salles = $salleRepository->findIdByBatiment($batimentNom);

        return new JsonResponse($salles);
    }

    /**
     * @author Axel
     * @brief (AJAX) Envoie la note au technicien en changeant son type
     * @param int $noteId la note à envoyer
     * @param NoteRepository $noteRepository pour accéder à l'entité à modifier
     * @param EntityManagerInterface $manager pour persist et flush les modifications
     * @return Response
     */
    #[Route('/request/dashboard/sendNote/{noteId}', name: 'request_sendNote', methods: ['GET'])]
    public function sendNote(int $noteId, NoteRepository $noteRepository ,EntityManagerInterface $manager): Response
    {
        $response = 'success'; // Par défaut initialisé à success

        $note = $noteRepository->find($noteId); // Récupère la note par son id
        // Si on ne trouve aucune note
        if (!$note)
        {
            $response = "Note introuvable";
            $this->addFLash('message', "Impossible de transmettre l'information' (information introuvable)");
        }
        // Si on trouve une note
        else
        {
            // On change son type, persist et flush l'entité
            $note->setType(TypeNote::ProblemeBatiment);
            $manager->persist($note);
            $manager->flush();

            $this->addFLash('message', "Information transmise!");
        }

        return new JsonResponse(
            $response,
        );
    }

    /**
     * @author Axel
     * @brief (AJAX) Envoie la note au technicien en changeant son type
     * @param int $noteId la note à envoyer
     * @param NoteRepository $noteRepository pour accéder à l'entité à modifier
     * @param EntityManagerInterface $manager pour persist et flush les modifications
     * @return Response
     */
    #[Route('/request/dashboard/ignoreNote/{noteId}', name: 'request_ignoreNote', methods: ['GET'])]
    public function ignoreNote(int $noteId, NoteRepository $noteRepository ,EntityManagerInterface $manager): Response
    {
        $response = 'success'; // Par défaut initialisé à success

        $note = $noteRepository->find($noteId); // Récupère la note par son id
        // Si on ne trouve aucune note
        if (!$note)
        {
            $response = "Note introuvable";
            $this->addFLash('message', "Impossible d'ignorer l'information' (information introuvable)");
        }
        // Si on trouve une note
        else
        {
            // On change son type, persist et flush l'entité
            $note->setType(TypeNote::ProblemeBatimentIgnore);
            $manager->persist($note);
            $manager->flush();

            $this->addFLash('message', "Information ignorée!");
        }

        return new JsonResponse(
            $response,
        );
    }
}