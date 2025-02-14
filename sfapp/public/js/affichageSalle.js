const redirectionButtonPlan = document.getElementById('redirect-plan-button'); // bouton de redirection
const redirectionButtonDiagnostic = document.getElementById('redirect-diagnostic-button'); // bouton de redirection

// Ajout de l'évènement au chargement de la page
document.addEventListener('DOMContentLoaded', function ()
{
    const listSalleContainer = document.getElementById('list-etage-container'); // Container où l'on affiche les salles
    const searchBar = document.getElementById('search-bar');

    // Etage séléctionné
    const etage = document.getElementById('list-etage');

    document.dispatchEvent(new Event('adminElementsAdded'));

    etage.value = etage.options[0].value;

    //Affiche la liste des salle avec l'étage par défaut et retourne la 'promesse' de réponse
    getAllSalleByEtageAndDisplay(etage.value, listSalleContainer);

    // Actualise la liste des salles à chaque changement d'étages
    etage.addEventListener('change', () =>
    {
        searchBar.value = '';

        getAllSalleByEtageAndDisplay(etage.value, listSalleContainer);
    })

    // Bare de recherche
    manageSearchBar(searchBar, listSalleContainer, etage);
});


/**
 * @author Axel
 * @brief Récupère toutes les salles se trouvant dans un étage puis
 *        appelle la fonction d'affichage des salles
 * @param idEtage Identifiant de l'étage pour la requête AJAX
 * @param listSalleContainer Container pour l'affichage des salles
 */
function getAllSalleByEtageAndDisplay(idEtage, listSalleContainer)
{
    fetch(`request/salle/findByEtage/${idEtage}`, {
        method:'GET',
        headers: {
            'X-Requested-With' : 'XMLHttpRequest' // REQUÊTE AJAX
        }

    })
        // si la réponse est négative
        .then(response => {
            if(!response.ok)
            {
                throw new Error(response.statusText);
            }
            return response.json();
        })
        .then(data =>
        {
            const salles = data.salles;
            listSalleContainer.innerHTML = ``;

            // Si aucune salle dans l'étage
            if (salles.length === 0)
            {
                const sallesContainer = document.createElement('div')
                sallesContainer.innerHTML =
                    `
                                <p>Aucune salle dans cet étage</p>
                            `
                listSalleContainer.appendChild(sallesContainer);
                document.dispatchEvent(new Event('adminElementsAdded'));
            }
            else
            {
                // Affiche toutes les salles dans l'étage
                displaySalles(salles);
                document.dispatchEvent(new Event('adminElementsAdded'));
            }
        })
}

/**
 * @author Axel
 * @brief Rend toutes les salles cliquables et appelle les fonctions
 *        nécessaires à l'affichage de leurs détails
 */
function handleSalleClicks()
{
    const SalleDetailsContainer = document.getElementById('salle-details'); // Container où l'on affiche le détails des salles
    const salles = document.querySelectorAll('#salle');
    let previousSalle;

    salles.forEach(salles =>
    {
        salles.addEventListener('click', () =>
        {
            // Récupère les conseils de la salle si elle en a
            // 0 = idSA, 1 = nomSalle, 2 = id Salle, 3 = nombre conseil
            const salleInfo = [
                salles.getAttribute('data-idSA-salle'),
                salles.getAttribute('data-nom-salle'),
                salles.getAttribute('data-id-salle'),
                salles.getAttribute("data-count-conseils"),
            ]

            // récupérations des bouttons etc
            const route = `/salle/modifier/${salleInfo[2]}`;
            const editButton = salles.querySelector('#edit-btn-salle');
            const deleteButton = salles.querySelector('#delete-btn-salle');
            const separator = salles.querySelector('.ligne-séparatrice-sa');

            SalleDetailsContainer.style.transform = 'scale(1)';


            // Ajout de l'évènement de clic au bouton de modification s'il existe
            if (editButton)
            {
                editButton.addEventListener('click', () => {
                    window.location.href = route;
                })
            }

            // Ajout de l'évènement de clic au bouton de suppression s'il existe
            if (deleteButton)
            {
                deleteButton.addEventListener('click', () => {
                    manageDeleteButton(salleInfo);
                });
            }

            // Désélectionne la salle précédente
            if (previousSalle) {
                const editButton = previousSalle.querySelector('#edit-btn-salle');
                const deleteButton = previousSalle.querySelector('#delete-btn-salle');
                const separator = previousSalle.querySelector('.ligne-séparatrice-sa');

                previousSalle.style.backgroundColor = salles.style.backgroundColor;
                previousSalle.style.color = salles.style.color;
                if (deleteButton)
                {
                    deleteButton.classList.remove('active');
                    deleteButton.classList.add('inactive');
                }

                if (editButton)
                {
                    editButton.classList.remove('active');
                    editButton.classList.add('inactive');
                }
                // ligne séparatrice
                separator.style.borderLeft = 'black  solid 0.15vw';
            }

            previousSalle = salles;
            salles.style.backgroundColor = '#666666';
            salles.style.color = '#FFFFFF';
            if (deleteButton)
            {
                deleteButton.classList.remove('inactive');
                deleteButton.classList.add('active');
            }

            if (editButton)
            {
                editButton.classList.remove('inactive');
                editButton.classList.add('active');
            }
            // ligne séparatrice
            separator.style.borderLeft = '#CACACA solid 0.15vw';

            getAndDisplaySalleDetails(salleInfo, SalleDetailsContainer)

        });
    })
}

/**
 * @author Axel
 * @brief Récupère les détails de la salle puis les affiches
 * @param salleInfo Tableau des informations de la salle (id de son SA,
 *                  son nom de salle, son id de salle)
 * @param SalleDetailsContainer Container pour l'affichage des détails
 */
function getAndDisplaySalleDetails(salleInfo, SalleDetailsContainer)
{
    // Variables
    const idSA = salleInfo[0];
    const nomSalle = salleInfo[1];
    const idSalle = salleInfo[2];
    const nbConseil = salleInfo[3];

    let conseilArray;
    // Si la salle a un conseil ou + on les récupère
    // if(nbConseil > 0)
    // {
        getConseilSalle(idSalle)
            .then(conseilResult =>
            {
                // Récupère les détails de la salle en asynchrone
                return getDetailsSalle(idSalle, idSA)
                    .then(result =>
                    {
                        displaySalleDetails(result, nomSalle, SalleDetailsContainer, conseilResult);
                        manageRedirection(salleInfo);
                    })
            })
    // }
    // else
    // {
    //     // Récupère les détails de la salle en asynchrone
    //     getDetailsSalle(idSalle, idSA)
    //         .then(result =>
    //         {
    //             displaySalleDetails(result, nomSalle, SalleDetailsContainer, conseilArray);
    //             manageRedirection(salleInfo);
    //         })
    // }

    // Requête AJAX pour récupérer les détails de la salle
    fetch(`/request/salle/getDetailsSalle/${idSalle}/${idSA}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest' // AJAX Request
        }
    })
        // Si la requête n'a pas eu de réponse valide
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.json();
        })
        // Si la requête renvoie une réponse
        .then(data => {
            displaySalleDetails(data, nomSalle, SalleDetailsContainer);
            manageRedirection(salleInfo);
        })
    .catch(error => {
        console.log(error);
    });
}

/**
 * @author Axel
 * @brief Gère le bouton supprimer (son affichage et ses actions)
 * @param salleInfo Tableau des informations de la salle (id de son SA,
 *                  son nom de salle, son id de salle)
 */
function manageDeleteButton(salleInfo)
{
    const confirmPopup = document.getElementById('message-confirmation');
    const confirmCancelButton = document.getElementById('confirm-cancel-button');
    const confirmDeleteButton = document.getElementById('confirm-delete-button');

    confirmPopup.style.display = 'block';

    confirmCancelButton.addEventListener('click', () => {
        confirmPopup.style.display = 'none';
    });

    // Confirmer la suppression
    confirmDeleteButton.addEventListener('click', () => {
        deleteRoom(salleInfo);

        // Ferme le pop-up
        confirmPopup.style.display = 'none';
    });

}

/**
 * @author Corentin
 * @param salleInfo Informations sur la salle à supprimer
 */
function deleteRoom (salleInfo){

    const nomSalle = salleInfo[1];
    fetch(`request/salle/deleteSalle/${nomSalle}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }

    })
        // si la requête ne fonctionne pas
        .then(response => {
            if(!response.ok)
            {
                throw new Error(response.statusText)
            }
            return response.json();
        })
        .then(data => {
            window.location.reload();
        })
        .catch(error => {
            console.log(error)
        })

}


/**
 * @author Axel
 * @brief Gére la barre de recherche en appellant les bonnes
 *        fonctions en fonction de son contenu
 * @param searchBar L'objet barre de recherche
 * @param listSalleContainer Le container où les salles sont affichées
 * @param etage L'étage actuellement sélectionné durant l'utilisation de la search bar
 */
function manageSearchBar(searchBar, listSalleContainer, etage)
{

    //Nettoie la valeur par défaut de la barre
    searchBar.value = '';

    searchBar.addEventListener('input', e => {
        if(searchBar.value.length !== 0) // Affiche la recherche la barre de recherche n'est pas vide
        {
            getSallesFromNameAndDisplay(searchBar.value, listSalleContainer)
        }
        else
        {
            getAllSalleByEtageAndDisplay(etage.value, listSalleContainer)
        }
    })
}

/**
 * @author Axel
 * @brief Récupère la liste des salles correspondant au nom se trouvant
 *        dans la barre de recherche puis appelle la fonction pour afficher les salles
 * @param salleName Le string utilisé dans la barre de recherche
 * @param listSalleContainer Le container où l'on affiche les salles
 */
function getSallesFromNameAndDisplay(salleName, listSalleContainer)
{
    fetch(`/request/salle/getSallesFromName/${salleName}`,  {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if(!response.ok)
        {
            throw new Error(response.statusText)
        }
        return response.json();
    })
    .then(data => {
        listSalleContainer.innerHTML = ``;

        const salles = data.salles;

        if (salles.length === 0) // Si aucun résultats
        {
            const sallesContainer = document.createElement('div')
            sallesContainer.innerHTML =
                `
                                <p>Aucune salle avec ce nom trouvée</p>
                `
            listSalleContainer.appendChild(sallesContainer);
        }
        else
        {
            // Affiche toutes les salles dans l'étage
            displaySalles(salles)
        }
    })
}

/**
 * @author Axel, Julien
 * @brief Fonction générale pour gérer l'affichage de toutes les salles
 *        passé en paramètres de fonction. Appelle la fonction pour les rendre
 *        cliquables juste après.
 * @param salles Liste des salles à afficher
 */
function displaySalles(salles)
{
    const listSalleContainer = document.getElementById('list-etage-container'); // Container où l'on affiche les salles

    // Affiche toutes les salles dans l'étage
    salles.forEach(salle =>
    {
        const sallesContainer = document.createElement('div');
        let nombreSA = salle.sa_count;


        // Ajout les attributs au container
        sallesContainer.id = "salle";
        sallesContainer.setAttribute("data-idSA-salle", salle.sa_id);
        sallesContainer.setAttribute("data-nom-salle", salle.nom);
        sallesContainer.setAttribute("data-id-salle", salle.id);
        sallesContainer.setAttribute("data-count-conseils", salle.nbConseils);

        if (nombreSA === 0 || nombreSA === "0")
        {
            nombreSA = "AUCUN";
        }

        sallesContainer.innerHTML =
            `
                <div class="salle-container-infos">
                    <p>SALLE ${ salle.nom }</p>
                    <div class="ligne-séparatrice-sa"></div>
                    <div id="salle-sa-attribue">SA ATTRIBUÉ :</div>
                    <p id="nombre-sa-attribue">${nombreSA}</p>
                </div>
            `;

        // Ajout des boutons si l'utilisateur est connecté
        if (rolesUtilisateur.includes('ROLE_TECHNICIEN') || rolesUtilisateur.includes('ROLE_CHARGEMISSION'))
        {
            let buttonsContainer = document.createElement("div");
            buttonsContainer.classList.add('buttons-display');
            buttonsContainer.innerHTML =
                `
                    <button id="delete-btn-salle" class="button-type3 button-cancel delete admin inactive">
                        <p class="txt">SUPPRIMER</p>
                        <div class="svg">
                            <svg fill ="white" height="1.15vw"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z"/></svg>
                        </div>
                    </button>
                    <button id="edit-btn-salle" class="button-type3 button-confirm modif admin inactive">
                        <p class="txt">MODIFIER</p>
                        <div class="svg">
                            <svg fill ="white" height="1.15vw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9
                                21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6
                                18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3
                                172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43
                                96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7
                                0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/>
                            </svg>
                        </div>
                    </button>
                `;
            sallesContainer.appendChild(buttonsContainer);
        }

        listSalleContainer.appendChild(sallesContainer);
    })

    handleSalleClicks();
}

/**
 * @author Axel
 * @brief Converti une date obtenue dans la base de données en chaîne de caractères au format "JJ/MM/AAAA"
 * @param dateInput La date sans traitement pour l'affichage
 * @returns {string} La date traitée
 */
function formatDateToCustomFormat(dateInput) {
    const date = new Date(dateInput);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear(); // Get full year
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

/**
 * @author Axel
 * @brief Affiche les détails de la salle suivant si, oui ou non, elle a un SA.
 * @param data Les données de la requête AJAX
 * @param nomSalle Le nom de la salle
 * @param SalleDetailsContainer Le container pour afficher les détails
 * @param conseilResult Tableau de la liste des conseils
 */
function displaySalleDetails(data,nomSalle, SalleDetailsContainer, conseilResult)
{
    //On crée une DIV pour afficher les données
    const SADetailsDiv = document.createElement('div');
    SADetailsDiv.classList.add('details-salle');
    const detailSalle = data.detailsSalle[0];

    // Div des conseils
    const conseilContainer = document.createElement('div');
    // Si il y a des conseils
    if (conseilResult !== undefined)
    {
        conseilContainer.className = 'advice';
        conseilContainer.innerHTML =
            `
            <div id="advice-title">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.19C2 19.83 4.17 22 7.81 22H16.19C19.83 22 22 19.83 22 16.19V7.81C22 4.17 19.83 2 16.19 2ZM9.97 14.9L7.72 17.15C7.57 17.3 7.38 17.37 7.19 17.37C7 17.37 6.8 17.3 6.66 17.15L5.91 16.4C5.61 16.11 5.61 15.63 5.91 15.34C6.2 15.05 6.67 15.05 6.97 15.34L7.19 15.56L8.91 13.84C9.2 13.55 9.67 13.55 9.97 13.84C10.26 14.13 10.26 14.61 9.97 14.9ZM9.97 7.9L7.72 10.15C7.57 10.3 7.38 10.37 7.19 10.37C7 10.37 6.8 10.3 6.66 10.15L5.91 9.4C5.61 9.11 5.61 8.63 5.91 8.34C6.2 8.05 6.67 8.05 6.97 8.34L7.19 8.56L8.91 6.84C9.2 6.55 9.67 6.55 9.97 6.84C10.26 7.13 10.26 7.61 9.97 7.9ZM17.56 16.62H12.31C11.9 16.62 11.56 16.28 11.56 15.87C11.56 15.46 11.9 15.12 12.31 15.12H17.56C17.98 15.12 18.31 15.46 18.31 15.87C18.31 16.28 17.98 16.62 17.56 16.62ZM17.56 9.62H12.31C11.9 9.62 11.56 9.28 11.56 8.87C11.56 8.46 11.9 8.12 12.31 8.12H17.56C17.98 8.12 18.31 8.46 18.31 8.87C18.31 9.28 17.98 9.62 17.56 9.62Z" fill="#2BA801"></path> </g></svg>
                <p>Conseils :</p>
            </div>
            `;

        // Sélectionne le tableau dans le resultat
        const conseilArray = conseilResult.conseils;


        // Affiche chaque conseil
        conseilArray.forEach(conseil =>
        {
            const conseilContainerItem = document.createElement('div');
            conseilContainerItem.id = 'advice-box';
            let typeColor = "";

            // Déterminer la couleur en fonction du type
            if (conseil.type === "Température") {
                typeColor = "color: #FD1900;";

            } else if (conseil.type === "CO2") {
                typeColor = "color: #A501A8;";

            } else if (conseil.type === "Humidité") {
                typeColor = "color: #4493D3;";

            } else if (conseil.type === "Luminosité") {
                typeColor = "color: #FDB900;";

            }
            conseilContainerItem.innerHTML =
                `
                <div id="conseil-type"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="19" viewBox="0 0 22 17" fill="none">
                        <path d="M11 12.2572H11.01M11 9.45008V6.64297M3.99835 16H18.0016C19.5416 16 20.5012 14.4333 19.7252 13.1857L12.7236 1.92846C11.9536 0.690514 10.0464 0.690514 9.2764 1.92846L2.27482 13.1857C1.49885 14.4333 2.45836 16 3.99835 16Z" stroke="#EC9A2F" stroke-opacity="0.8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p style="${typeColor}">${conseil.type}</p>
                </div>
                <div id="conseil-description">
                    <p>${conseil.description}</p>
                </div>
                <div id="conseil-text">
                    <p>${conseil.text}</p>
                </div>
                `
            conseilContainer.appendChild(conseilContainerItem);
        })
    }

    // Si aucun SA
    if(data.capteurs === null || data.capteurs.length === 0  )
    {
        const nomCompletEtage = detailSalle.nomEtage;
        SADetailsDiv.innerHTML =
            `
                        <div class="titre">
                            <h3>SALLE ${nomSalle}</h3>
                            <h4> ${nomCompletEtage} </h4></div>
                        <div class="info-salle-without-sa">
                              <h3>Aucun SA attribué</h3>
                        </div>
                    
                        <!-- Affichage des détails -->
                        <div class="info-detail-salle">
                            <div class="section1">
                                <div class="ligne">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1.46vw" height="1.46vw" viewBox="0 0 24 24" fill="#2BA801">
                                        <path d="M3 5V19C3 20.103 3.897 21 5 21H19C20.103 21 21 20.103 21 19V5C21 3.897 20.103 3 19 3H5C3.897 3 3 3.897 3 5ZM19.002 19H5V5H19L19.002 19Z" /><path d="M15 12H17V7H12V9H15V12ZM12 15H9V12H7V17H12V15Z"/>
                                        </svg>
                                    <span>Superficie : </span>
                                    <span class="donnée">${detailSalle.superficie || '?'}</span>
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 19 19" width="1.46vw" height="1.46vw" xmlns="http://www.w3.org/2000/svg" fill="#2BA801"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M443,113.5 C443,113.223858 443.215753,113 443.495389,113 L445.504611,113 C445.778207,113 446,113.231934 446,113.5 C446,113.776142 445.784247,114 445.504611,114 L443.495389,114 C443.221793,114 443,113.768066 443,113.5 Z M427,113.5 C427,113.223858 427.215753,113 427.495389,113 L429.504611,113 C429.778207,113 430,113.231934 430,113.5 C430,113.776142 429.784247,114 429.504611,114 L427.495389,114 C427.221793,114 427,113.768066 427,113.5 Z M436,120.495389 C436,120.221793 436.231934,120 436.5,120 C436.776142,120 437,120.215753 437,120.495389 L437,122.504611 C437,122.778207 436.768066,123 436.5,123 C436.223858,123 436,122.784247 436,122.504611 L436,120.495389 Z M436,104.495389 C436,104.221793 436.231934,104 436.5,104 C436.776142,104 437,104.215753 437,104.495389 L437,106.504611 C437,106.778207 436.768066,107 436.5,107 C436.223858,107 436,106.784247 436,106.504611 L436,104.495389 Z M441.096194,118.096194 C441.291456,117.900932 441.602308,117.895201 441.80004,118.092934 L443.220775,119.513668 C443.414236,119.70713 443.407066,120.027963 443.217514,120.217514 C443.022252,120.412777 442.711401,120.418508 442.513668,120.220775 L441.092934,118.80004 C440.899472,118.606579 440.906643,118.285746 441.096194,118.096194 Z M429.782486,106.782486 C429.977748,106.587223 430.288599,106.581492 430.486332,106.779225 L431.907066,108.19996 C432.100528,108.393421 432.093357,108.714254 431.903806,108.903806 C431.708544,109.099068 431.397692,109.104799 431.19996,108.907066 L429.779225,107.486332 C429.585764,107.29287 429.592934,106.972037 429.782486,106.782486 Z M431.19996,118.092934 C431.393421,117.899472 431.714254,117.906643 431.903806,118.096194 C432.099068,118.291456 432.104799,118.602308 431.907066,118.80004 L430.486332,120.220775 C430.29287,120.414236 429.972037,120.407066 429.782486,120.217514 C429.587223,120.022252 429.581492,119.711401 429.779225,119.513668 L431.19996,118.092934 Z M442.513668,106.779225 C442.70713,106.585764 443.027963,106.592934 443.217514,106.782486 C443.412777,106.977748 443.418508,107.288599 443.220775,107.486332 L441.80004,108.907066 C441.606579,109.100528 441.285746,109.093357 441.096194,108.903806 C440.900932,108.708544 440.895201,108.397692 441.092934,108.19996 L442.513668,106.779225 Z M436.5,118 C438.985281,118 441,115.985281 441,113.5 C441,111.014719 438.985281,109 436.5,109 C434.014719,109 432,111.014719 432,113.5 C432,115.985281 434.014719,118 436.5,118 Z M436.5,117 C438.432997,117 440,115.432997 440,113.5 C440,111.567003 438.432997,110 436.5,110 L436.5,117 Z" transform="translate(-427 -104)"></path> </g></svg>
                                    <span>Exposition : </span>
                                    <span class="donnée">${detailSalle.exposition || '?'}</span>
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 16 16" width="1.46vw" height="1.46vw" xmlns="http://www.w3.org/2000/svg" fill="#2BA801" class="bi bi-people-fill"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path> <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"></path> <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"></path></svg>
                                    <span>Fréquentation : </span>
                                    <span class="donnée">${detailSalle.frequentation || '?'}</span>
                                </div>
                            </div>
                            <div class="section2">
                                <div class="ligne">
                                    <svg fill="#2BA801" height="1.40vw" width="1.40vw" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M456.348,33.391h-50.087v100.174h50.087c9.223,0,16.696-7.473,16.696-16.696V50.087 C473.043,40.864,465.57,33.391,456.348,33.391z"></path> </g> </g> <g> <g> <path d="M55.652,0C28.033,0,5.565,22.468,5.565,50.087v378.435c0,27.619,22.468,50.087,50.087,50.087 c27.619,0,50.087-22.468,50.087-50.087V50.087C105.739,22.468,83.271,0,55.652,0z"></path> </g> </g> <g> <g> <path d="M189.217,0c-27.619,0-50.087,22.468-50.087,50.087v378.435c0,27.619,22.468,50.087,50.087,50.087 c27.619,0,50.087-22.468,50.087-50.087V50.087C239.304,22.468,216.836,0,189.217,0z"></path> </g> </g> <g> <g> <path d="M322.783,0c-27.619,0-50.087,22.468-50.087,50.087v378.435c0,27.619,22.468,50.087,50.087,50.087 c27.619,0,50.087-22.468,50.087-50.087V50.087C372.87,22.468,350.402,0,322.783,0z"></path> </g> </g> <g> <g> <path d="M456.348,345.043h-50.087v100.174v16.696c0,27.619,22.468,50.087,50.087,50.087c27.619,0,50.087-22.468,50.087-50.087 V395.13C506.435,367.511,483.967,345.043,456.348,345.043z"></path> </g> </g> </g></svg>
                                    <span>Radiateur : </span>
                                    <span class="donnée">${detailSalle.radiateur || '?'}</span>
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"  width="1.46vw" height="1.46vw" id="window" fill="#2BA801" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M27 23V3H5v20H3v6h26v-6H27zM17 15h8v8h-8V15zM25 5v8h-8V5H25zM7 5h8v8H7V5zM7 15h8v8H7V15zM27 27H5v-2h22V27z"></path> </g></svg>
                                    <span>Fenêtres : </span>
                                    <span class="donnée">${detailSalle.fenetre || '?'}</span>
                                    
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 20 20" width="1.46vw" height="1.46vw" xmlns="http://www.w3.org/2000/svg"  fill="#2BA801"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>door [#44]</title> <desc>Created with Sketch.</desc> <defs> </defs> <g id="Page-1" stroke="none" stroke-width="1"  fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-220.000000, -7999.000000)" > <g id="icons" transform="translate(56.000000, 160.000000)"> <path d="M173.25,7849.125 C173.25,7849.677 172.802,7850.125 172.25,7850.125 C171.698,7850.125 171.25,7849.677 171.25,7849.125 C171.25,7848.573 171.698,7848.125 172.25,7848.125 C172.802,7848.125 173.25,7848.573 173.25,7849.125 L173.25,7849.125 Z M178,7857 L170,7857 L170,7842 C170,7841.448 170.448,7841 171,7841 L177,7841 C177.552,7841 178,7841.448 178,7842 L178,7857 Z M183,7857 L180,7857 L180,7841 C180,7839.895 179.105,7839 178,7839 L170,7839 C168.896,7839 168,7839.895 168,7841 L168,7857 L165,7857 C164.448,7857 164,7857.448 164,7858 C164,7858.552 164.448,7859 165,7859 L183,7859 C183.552,7859 184,7858.552 184,7858 C184,7857.448 183.552,7857 183,7857 L183,7857 Z" id="door-[#44]"> </path> </g> </g> </g> </g></svg>
                                    <span>Portes : </span>
                                    <span class="donnée">${detailSalle.porte || '?'}</span>
                                </div>
                            </div>
                        </div>
                `;

        //cacher les boutons de redirection
        if(redirectionButtonPlan)
            redirectionButtonPlan.style.transform = "scale(0)";
        if(redirectionButtonDiagnostic)
            redirectionButtonDiagnostic.style.transform = "scale(0)";
    }
    else
    {
        const capteurs = data.capteurs;
        const nomCompletEtage = data.NomSA[0].nomEtage;

        let co2Sensor, tempSensor, humidSensor, lumSensor;
        capteurs.forEach(sensor => {
            switch (sensor.type) {
                case 'Température':
                    tempSensor = sensor;
                    break;
                case 'CO2':
                    co2Sensor = sensor;
                    break;
                case 'Humidité':
                    humidSensor = sensor;
                    break;
                case "Luminosité":
                    lumSensor = sensor;
                    break;
                default:
                    console.log('Capteur inconnu:', sensor);
            }
        });

        if(tempSensor.date === null)
        {
            date = "Aucune date";
        }
        else date= formatDateToCustomFormat( tempSensor.date.date);

        let isLuminosity = "Oui";

        // Si capteurs luminosité à 0
        if(lumSensor.valeur === 0 || lumSensor.valeur === "0")
        {
            isLuminosity = "Non";
        }
        else if(lumSensor.valeur === -1 || lumSensor.valeur === null)
        {
            isLuminosity = "?";
        }
        if(tempSensor.valeur === -41 || tempSensor.valeur === null)
        {
            temp="?";
        }
        else temp=tempSensor.valeur + " °C";
        if(co2Sensor.valeur === -1 || co2Sensor.valeur === null)
        {
            co2="?"
        }
        else co2=co2Sensor.valeur + " ppm";
        if(humidSensor.valeur === -1 || humidSensor.valeur === null)
        {
            humid="?";
        }
        else humid=humidSensor.valeur + " %";


        SADetailsDiv.innerHTML =
            `
                        <div class="titre">
                            <h3>SALLE ${nomSalle}</h3>
                            <h4> ${nomCompletEtage} </h4></div>
                        <div class="info-salle">
                            <div class="donnee-date"> 
                                Données des capteurs
                                <div class="date">
                                    ${date}
                                </div>
                            </div>
                            <div class="sous-info-salle">
                                <div class ="temperature-sense">
                                    <div style="width : 2vw ; height: 0.9vw ">
                                        <svg width="23" height="34" viewBox="0 0 23 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.7437 26.8165C11.7137 26.8165 12.5 26.1777 12.5 25.3896C12.5 24.6015 11.7137 23.9626 10.7437 23.9626C9.77366 23.9626 8.9873 24.6015 8.9873 25.3896C8.9873 26.1777 9.77366 26.8165 10.7437 26.8165Z" stroke="#FD1900" stroke-opacity="0.81" stroke-width="3" stroke-miterlimit="10"/> <path d="M17.75 21.1706V6.90111C17.6563 5.44483 16.8783 4.0733 15.5764 3.06941C14.2745 2.06552 12.5481 1.50586 10.7532 1.50586C8.95827 1.50586 7.23181 2.06552 5.92993 3.06941C4.62804 4.0733 3.85003 5.44483 3.75636 6.90111V21.1706C2.62331 22.3849 2.00688 23.8656 2 25.3894C2.10514 27.218 3.07327 28.9435 4.70329 30.2074C6.33332 31.4713 8.49995 32.1764 10.7532 32.1764C13.0064 32.1764 15.173 31.4713 16.8031 30.2074C18.4331 28.9435 19.4012 27.218 19.5064 25.3894C19.4995 23.8656 18.8831 22.3849 17.75 21.1706Z" stroke="#FD1900" stroke-opacity="0.81" stroke-width="3" stroke-miterlimit="10"/>
                                        <path d="M10.7437 6.90112V23.9625" stroke="#FD1900" stroke-opacity="0.81" stroke-width="3" stroke-miterlimit="10"/> <path d="M17.75 8.32812H23" stroke="#FD1900" stroke-opacity="0.81" stroke-width="3" stroke-miterlimit="10"/> <path d="M17.75 14.0203H23" stroke="#FD1900" stroke-opacity="0.81" stroke-width="3" stroke-miterlimit="10"/><path d="M17.75 19.6973H23" stroke="#FD1900" stroke-opacity="0.81" stroke-width="3" stroke-miterlimit="10"/>
                                        </svg>
                                    </div>  
                                    <div style="color : #FD1900 ; margin-right: 0.8vw" class="sous-info-salle_separator"></div>
                                    <p>Température :</p>
                                    <p  style = "font-weight : 600 ; color :  #FD1900"> ${temp} </p>
                                   
                                   
                                    
                                    
                                </div>
                                <div class ="co2-sense">
                                    <div style="width : 2vw ; height: 0.9vw">
                                        <svg width="32" height="23" viewBox="0 0 32 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.3333 12.763H20.6667M10.4 20.7293C5.7608 20.7293 2 17.5486 2 13.6251C2 10.3732 4.8 7.36905 9 6.78817C10.3175 4.11461 13.4355 2.14111 17.0722 2.14111C21.7307 2.14111 25.5382 5.22478 25.8 9.1117C28.2722 10.0343 30 12.2987 30 14.7504C30 18.0525 26.866 20.7293 23 20.7293H10.4Z" stroke="#A501A8" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>  
                                    <div style="color : #A501A8 ; margin-right: 0.8vw" class="sous-info-salle_separator"></div>
                                    <p>C02 :</p>
                                    <p style = "font-weight : 600 ; color :  #A501A8"> ${co2} </p>
                                   
                                  
                                      
                                </div>
                                <div class ="humidite-sense">
                                    <div style="width : 2vw ; height: 0.9vw">
                                        <svg width="27" height="30" viewBox="0 0 27 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.4442 18.921C11.4442 18.2691 10.8389 17.7407 10.0922 17.7407C9.34552 17.7407 8.74023 18.2691 8.74023 18.921C8.74023 20.5232 9.10151 22.1521 10.2036 23.4029C11.3554 24.7102 13.1437 25.4127 15.5001 25.4127C16.2468 25.4127 16.852 24.8843 16.852 24.2324C16.852 23.5805 16.2468 23.0521 15.5001 23.0521C13.8005 23.0521 12.8848 22.5743 12.3468 21.9636C11.7589 21.2964 11.4442 20.2696 11.4442 18.921Z" fill="#4493D3"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.7581 1.50701C14.5337 0.423085 12.4652 0.423128 11.241 1.50709C9.74318 2.83313 6.97894 5.40571 4.57926 8.33945C2.1848 11.2668 -0.0385821 14.6008 0.000507891 18.2222C0.00395187 18.5425 0.0246794 18.9921 0.0925909 19.5284C0.227478 20.5936 0.553275 22.0394 1.33279 23.5045C2.11573 24.976 3.37231 26.4946 5.37688 27.6411C7.39056 28.7929 10.0489 29.5059 13.4995 29.5059C16.9502 29.5059 19.6086 28.7929 21.6224 27.6411C23.6269 26.4946 24.8837 24.9762 25.6668 23.5047C26.4464 22.0396 26.7724 20.5936 26.9073 19.5284C26.9752 18.9922 26.996 18.5427 26.9995 18.2223C27.0388 14.6009 24.8149 11.2666 22.4204 8.33937C20.0204 5.40562 17.256 2.83304 15.7581 1.50701ZM7.21321 9.7958C9.44624 7.06581 12.0394 4.63913 13.4995 3.34262C14.9598 4.63912 17.5533 7.0658 19.7865 9.7958C21.8086 12.2677 23.8588 15.1269 23.8255 18.199C23.8228 18.4495 23.8063 18.8156 23.7502 19.258C23.6372 20.15 23.3679 21.3139 22.7588 22.4586C22.153 23.5968 21.2274 24.688 19.8097 25.4988C18.4013 26.3044 16.3974 26.8961 13.4995 26.8961C10.6017 26.8961 8.59797 26.3044 7.18956 25.4988C5.77204 24.688 4.84649 23.5968 4.24089 22.4586C3.63185 21.3139 3.36264 20.1501 3.24969 19.2581C3.19368 18.8156 3.17726 18.4496 3.17456 18.1991C3.14142 15.1271 5.19113 12.2679 7.21321 9.7958Z" fill="#4493D3"/>
                                        </svg>
                                    </div>
                                    <div style="color : #4493D3 ; margin-right: 0.8vw" class="sous-info-salle_separator"></div>
                                    <p>Humidité:</p>
                                    <p style = "font-weight : 600 ; color :  #4493D3"> ${humid} </p>
                                    
                                    
                               </div>
                                <div class ="luminosite-sense">
                                    <div style="width : 2vw ; height: 0.9vw">
                                        <svg width="31" height="29" viewBox="0 0 31 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.5 2V3.38889M15.5 25.6111V27M3.5 14.5H2M6.97118 6.60294L5.75 5.47222M24.0288 6.60294L25.25 5.47222M6.97118 22.4028L5.75 23.5279M24.0288 22.4028L25.25 23.5279M29 14.5H27.5M21.5 14.5C21.5 17.5682 18.8137 20.0556 15.5 20.0556C12.1863 20.0556 9.5 17.5682 9.5 14.5C9.5 11.4317 12.1863 8.94444 15.5 8.94444C18.8137 8.94444 21.5 11.4317 21.5 14.5Z" stroke="#FDB900" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                     </div>  
                                    <div style="color : #FDB900 ; margin-right: 0.8vw" class="sous-info-salle_separator"></div>
                                    <p>Lumineux:</p>
                                    <p style = "font-weight : 600 ; color :  #FDB900">${isLuminosity}</p>
                                   
                                   
                               </div>
                                    
                                
                            </div>  
                        </div>
                    
                        <!-- Affichage des détails -->
                        <div class="info-detail-salle">
                            <div class="section1">
                                <div class="ligne">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1.46vw" height="1.46vw" viewBox="0 0 24 24" fill="#2BA801">
                                        <path d="M3 5V19C3 20.103 3.897 21 5 21H19C20.103 21 21 20.103 21 19V5C21 3.897 20.103 3 19 3H5C3.897 3 3 3.897 3 5ZM19.002 19H5V5H19L19.002 19Z" /><path d="M15 12H17V7H12V9H15V12ZM12 15H9V12H7V17H12V15Z"/>
                                        </svg>
                                    <span>Superficie : </span>
                                    <span class="donnée">${detailSalle.superficie || '?'}</span>
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 19 19" width="1.46vw" height="1.46vw" xmlns="http://www.w3.org/2000/svg" fill="#2BA801"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M443,113.5 C443,113.223858 443.215753,113 443.495389,113 L445.504611,113 C445.778207,113 446,113.231934 446,113.5 C446,113.776142 445.784247,114 445.504611,114 L443.495389,114 C443.221793,114 443,113.768066 443,113.5 Z M427,113.5 C427,113.223858 427.215753,113 427.495389,113 L429.504611,113 C429.778207,113 430,113.231934 430,113.5 C430,113.776142 429.784247,114 429.504611,114 L427.495389,114 C427.221793,114 427,113.768066 427,113.5 Z M436,120.495389 C436,120.221793 436.231934,120 436.5,120 C436.776142,120 437,120.215753 437,120.495389 L437,122.504611 C437,122.778207 436.768066,123 436.5,123 C436.223858,123 436,122.784247 436,122.504611 L436,120.495389 Z M436,104.495389 C436,104.221793 436.231934,104 436.5,104 C436.776142,104 437,104.215753 437,104.495389 L437,106.504611 C437,106.778207 436.768066,107 436.5,107 C436.223858,107 436,106.784247 436,106.504611 L436,104.495389 Z M441.096194,118.096194 C441.291456,117.900932 441.602308,117.895201 441.80004,118.092934 L443.220775,119.513668 C443.414236,119.70713 443.407066,120.027963 443.217514,120.217514 C443.022252,120.412777 442.711401,120.418508 442.513668,120.220775 L441.092934,118.80004 C440.899472,118.606579 440.906643,118.285746 441.096194,118.096194 Z M429.782486,106.782486 C429.977748,106.587223 430.288599,106.581492 430.486332,106.779225 L431.907066,108.19996 C432.100528,108.393421 432.093357,108.714254 431.903806,108.903806 C431.708544,109.099068 431.397692,109.104799 431.19996,108.907066 L429.779225,107.486332 C429.585764,107.29287 429.592934,106.972037 429.782486,106.782486 Z M431.19996,118.092934 C431.393421,117.899472 431.714254,117.906643 431.903806,118.096194 C432.099068,118.291456 432.104799,118.602308 431.907066,118.80004 L430.486332,120.220775 C430.29287,120.414236 429.972037,120.407066 429.782486,120.217514 C429.587223,120.022252 429.581492,119.711401 429.779225,119.513668 L431.19996,118.092934 Z M442.513668,106.779225 C442.70713,106.585764 443.027963,106.592934 443.217514,106.782486 C443.412777,106.977748 443.418508,107.288599 443.220775,107.486332 L441.80004,108.907066 C441.606579,109.100528 441.285746,109.093357 441.096194,108.903806 C440.900932,108.708544 440.895201,108.397692 441.092934,108.19996 L442.513668,106.779225 Z M436.5,118 C438.985281,118 441,115.985281 441,113.5 C441,111.014719 438.985281,109 436.5,109 C434.014719,109 432,111.014719 432,113.5 C432,115.985281 434.014719,118 436.5,118 Z M436.5,117 C438.432997,117 440,115.432997 440,113.5 C440,111.567003 438.432997,110 436.5,110 L436.5,117 Z" transform="translate(-427 -104)"></path> </g></svg>
                                    <span>Exposition : </span>
                                    <span class="donnée">${detailSalle.exposition || '?'}</span>
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 16 16" width="1.46vw" height="1.46vw" xmlns="http://www.w3.org/2000/svg" fill="#2BA801" class="bi bi-people-fill"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path> <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"></path> <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"></path></svg>
                                    <span>Fréquentation : </span>
                                    <span class="donnée">${detailSalle.frequentation || '?'}</span>
                                </div>
                            </div>
                            <div class="section2">
                                <div class="ligne">
                                    <svg fill="#2BA801" height="1.40vw" width="1.40vw" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M456.348,33.391h-50.087v100.174h50.087c9.223,0,16.696-7.473,16.696-16.696V50.087 C473.043,40.864,465.57,33.391,456.348,33.391z"></path> </g> </g> <g> <g> <path d="M55.652,0C28.033,0,5.565,22.468,5.565,50.087v378.435c0,27.619,22.468,50.087,50.087,50.087 c27.619,0,50.087-22.468,50.087-50.087V50.087C105.739,22.468,83.271,0,55.652,0z"></path> </g> </g> <g> <g> <path d="M189.217,0c-27.619,0-50.087,22.468-50.087,50.087v378.435c0,27.619,22.468,50.087,50.087,50.087 c27.619,0,50.087-22.468,50.087-50.087V50.087C239.304,22.468,216.836,0,189.217,0z"></path> </g> </g> <g> <g> <path d="M322.783,0c-27.619,0-50.087,22.468-50.087,50.087v378.435c0,27.619,22.468,50.087,50.087,50.087 c27.619,0,50.087-22.468,50.087-50.087V50.087C372.87,22.468,350.402,0,322.783,0z"></path> </g> </g> <g> <g> <path d="M456.348,345.043h-50.087v100.174v16.696c0,27.619,22.468,50.087,50.087,50.087c27.619,0,50.087-22.468,50.087-50.087 V395.13C506.435,367.511,483.967,345.043,456.348,345.043z"></path> </g> </g> </g></svg>
                                    <span>Radiateur : </span>
                                    <span class="donnée">${detailSalle.radiateur || '?'}</span>
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"  width="1.46vw" height="1.46vw" id="window" fill="#2BA801" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M27 23V3H5v20H3v6h26v-6H27zM17 15h8v8h-8V15zM25 5v8h-8V5H25zM7 5h8v8H7V5zM7 15h8v8H7V15zM27 27H5v-2h22V27z"></path> </g></svg>
                                    <span>Fenêtres : </span>
                                    <span class="donnée">${detailSalle.fenetre || '?'}</span>
                                    
                                </div>
                                <div class="ligne">
                                    <svg viewBox="0 0 20 20" width="1.46vw" height="1.46vw" xmlns="http://www.w3.org/2000/svg"  fill="#2BA801"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>door [#44]</title> <desc>Created with Sketch.</desc> <defs> </defs> <g id="Page-1" stroke="none" stroke-width="1"  fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-220.000000, -7999.000000)" > <g id="icons" transform="translate(56.000000, 160.000000)"> <path d="M173.25,7849.125 C173.25,7849.677 172.802,7850.125 172.25,7850.125 C171.698,7850.125 171.25,7849.677 171.25,7849.125 C171.25,7848.573 171.698,7848.125 172.25,7848.125 C172.802,7848.125 173.25,7848.573 173.25,7849.125 L173.25,7849.125 Z M178,7857 L170,7857 L170,7842 C170,7841.448 170.448,7841 171,7841 L177,7841 C177.552,7841 178,7841.448 178,7842 L178,7857 Z M183,7857 L180,7857 L180,7841 C180,7839.895 179.105,7839 178,7839 L170,7839 C168.896,7839 168,7839.895 168,7841 L168,7857 L165,7857 C164.448,7857 164,7857.448 164,7858 C164,7858.552 164.448,7859 165,7859 L183,7859 C183.552,7859 184,7858.552 184,7858 C184,7857.448 183.552,7857 183,7857 L183,7857 Z" id="door-[#44]"> </path> </g> </g> </g> </g></svg>
                                    <span>Portes : </span>
                                    <span class="donnée">${detailSalle.porte || '?'}</span>
                                </div>
                            </div>
                        </div>
                `;

        //afficher les boutons de redirection
        if(redirectionButtonPlan)
            redirectionButtonPlan.style.transform = "scale(1)";
        if(redirectionButtonDiagnostic)
            redirectionButtonDiagnostic.style.transform = "scale(1)";
    }


    SalleDetailsContainer.innerHTML = ``;
    SalleDetailsContainer.appendChild(SADetailsDiv);
    SADetailsDiv.appendChild(conseilContainer)
}

/**
 * @author Victor
 * @brief Gère la redirection vers le plan de la salle sélectionnée
 * @param salleInfo Tableau des informations de la salle (id de son SA,
 *                  son nom de salle, son id de salle)
 */
function manageRedirection(salleInfo)
{
    let idSalle = salleInfo[2];
    if(redirectionButtonPlan)
    {
        redirectionButtonPlan.addEventListener('click', () => {
            window.location.href = `/plan/modifier/${idSalle}`;
        });
    }
    let nomSalle = salleInfo[1];
    if(redirectionButtonDiagnostic)
    {
        redirectionButtonDiagnostic.addEventListener('click', () => {
            window.location.href = `/diagnostic/${nomSalle}`;
        });
    }
}

/**
 * @author Axel
 * @brief utilise une requête AJAX en asynchrone pour avoir le/les conseils de la salle
 * @param idSalle
 */
async function getConseilSalle(idSalle)
{
    const result = await fetch(`/request/salle/getConseilSalle/${idSalle}`, {
    method:'GET',
        headers:
            {
            'X-Requested-With': 'XMLHttpRequest' // REQUÊTE AJAX
            }
    })
        .then(response => {
            if(!response.ok)
            {
                throw new Error(response.statusText);
            }
            return response.json();
        })
        .then(data =>
        {
            return data;
        })
        .catch(error =>
        {
            console.log(error);
        })

    return result;
}

async function getDetailsSalle(idSalle, idSA)
{
    // Requête AJAX pour récupérer les détails de la salle
    const result = await fetch(`/request/salle/getDetailsSalle/${idSalle}/${idSA}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest' // AJAX Request
        }
    })
        // Si la requête n'a pas eu de réponse valide
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.json();
        })
        // Si la requête renvoie une réponse
        .then(data => {
            return data
        })
        .catch(error => {
            console.log(error);
        });

    return result;
}