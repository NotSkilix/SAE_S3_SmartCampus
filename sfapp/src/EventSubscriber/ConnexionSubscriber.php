<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class ConnexionSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    /**
     * @author Julien
     * @brief Constructeur du subscriber.
     * @param RequestStack $requestStack Stack de la requête permettant de récupérer la session
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @author Julien
     * @brief Surcharge de la fonction renvoyant les évènements écoutés par le subscriber.
     * @return string[] Liste des évènements interceptés
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class => 'onLogout',
        ];
    }

    /**
     * @author Julien
     * @brief Ajoute au flash bag de la session la popup pour confirmer la connexion.
     * @param LoginSuccessEvent $event Évènement de succès de la connexion
     * @return void
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $session?->getFlashBag()->add('message', "Vous êtes maintenant connecté.");
    }

    /**
     * @author Julien
     * @brief Ajoute au flash bag de la session la popup pour signaler le problème de connexion.
     * @param LoginFailureEvent $event Évènement d'échec de la connexion
     * @return void
     */
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $session?->getFlashBag()->add('message', "Les identifiants sont incorrects.");
    }

    /**
     * @author Julien
     * @brief Ajoute au flash bag de la session la popup pour confirmer la déconnexion.
     * @param LogoutEvent $event Évènement de déconnexion
     * @return void
     */
    public function onLogout(LogoutEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $session?->getFlashBag()->add('message', "Vous avez été déconnecté.");
    }
}
