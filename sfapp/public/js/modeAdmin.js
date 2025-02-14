// Définition des variables globales
let adminElements;
let espaceConnexionTexte;

// Ajout de l'évènement au chargement de la page
document.addEventListener("DOMContentLoaded", () => {

    if ((location.pathname.startsWith("/salle/modifier")) || (location.pathname.startsWith("/salle/nouveau")))
    {
        checkAdminRoleOnNewPage();
    }
    else if (location.pathname.startsWith("/salle"))
    {
        document.addEventListener('adminElementsAdded', () => {
            adminRoleManager();
            checkAdminRoleOnNewPage();
        });
    }

    else 
    {
        adminRoleManager();
        checkAdminRoleOnNewPage();
    }
});

/**
 * @author Victor
 * @brief Passe en mode administrateur quand on clique sur le profil
 */
function adminRoleManager()
{
    // Récupère tous les élèments accessible par l'admin seulement
    adminElements = document.querySelectorAll('.admin');
    //adminElements.forEach(temp=>{console.log(temp)});

    espaceConnexionTexte = document.getElementById('espace-connexion-texte');
    // Récupère le container du profil
    let profilContainer = document.getElementById('espace-connexion');

    // Rend le profile cliquable:
    // Ajout de l'évènement de clic pour rendre le profil cliquable
    profilContainer.addEventListener('click', addEvent);
}

/**
 * @author Victor
 * @brief Affiche les boutons admins lorsque l'on recharge une page et que nous avons les bons droits
 */
function checkAdminRoleOnNewPage()
{
    // Récupère tous les élèments accessible par l'admin seulement
    let adminElements = document.querySelectorAll('.admin');

    let espaceConnexionTexte = document.getElementById('espace-connexion-texte');

    if(sessionStorage.getItem('admin') === "true")
    {
        adminElements.forEach(element =>
        {

            element.style.display = 'flex';

        });
        espaceConnexionTexte.innerHTML = 'Chargé de mission';
    }
    else
    {
        sessionStorage.setItem("admin", "false");
        adminElements.forEach(element =>
        {

            element.style.display = 'none';
        });
    }
}

/**
 * @author Victor
 * @brief Vérifie si la route utilisée est uniquement accessible en mode admin et charge une nouvelle page
 */
function checkRoute()
{
    const currentRoot = location.pathname // Après le "localhost:8000"

    // Regarde si la route commence bien par "systeme_acquisition"
    if(currentRoot.startsWith("/systeme_acquisition") || currentRoot.startsWith("/plan"))
    {
        // Charge la page d'accueil pour sortir des zones admins
        window.location.href = "/"
    }
}

/**
 * @author Victor
 * @brief Active le mode admin.
 */
function activateAdminMode()
{
    sessionStorage.setItem('admin', "true");

    adminElements.forEach(element =>
    {
        element.style.display = 'flex';
    });

    //exception pour le texte de l'espace de connexion
    espaceConnexionTexte.innerHTML = "Chargé de mission";
}

/**
 * @author Victor
 * @brief Désactive le mode admin.
 */
function deactivateAdminMode()
{
    sessionStorage.setItem('admin', "false");

    adminElements.forEach(element =>
    {
        element.style.display = 'none';

        //exception pour le texte "se connecter"
        espaceConnexionTexte.innerHTML = "Se connecter";

    });
}

/**
 * @author Victor
 * @brief
 */
function addEvent()
{
    if(sessionStorage.getItem('admin') === "false")
    {
        activateAdminMode();

    }
    else
    {
        checkRoute();
        deactivateAdminMode();

    }
}