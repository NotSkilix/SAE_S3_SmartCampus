// Ajout de l'évènement au chargement de la page
document.addEventListener('DOMContentLoaded', function ()
{
    const listSalleContainer = document.getElementById('plan-list'); // Container où l'on affiche les salles
    const searchBar = document.getElementById('search-bar');

    //Récupere l'état sélectionné via le dashboard si il y'en a un
    const etatDashboard = localStorage.getItem('etat'); // Récupère l'état depuis localStorage
    if (etatDashboard) {
        redirectionFromPlan (etatDashboard,listSalleContainer);
    }
    localStorage.removeItem('etat');


    // Récupère l'état sélectionné
    const etat = document.getElementById('list-etat');

    // Séléctionne par défaut toute les salles (état : "tout")
    etat.value = etat.options[0].value;

    // Affichage des salles en fonction de l'état séléctionné
    displaySalleByEtat(etat.value, listSalleContainer);

    // Actualise la liste des salles à chaque changement d'étages
    etat.addEventListener('change', () =>
    {
        searchBar.value = '';

        displaySalleByEtat(etat.value, listSalleContainer);
    })

    // Ajout de l'évènement de saisie de valeur dans la barre de recherche
    searchBar.addEventListener('input', e => {
        if(searchBar.value.length !== 0) // Affiche la recherche la barre de recherche n'est pas vide
        {
            displaySalleBySearch(searchBar.value, listSalleContainer);
        }
        else
        {
            displaySalleByEtat(etat.value, listSalleContainer);
        }
    })
});

/**
 * @author Corentin
 * @brief Permet d'appeller la fonction displaySalleByEtat via l'état sélectionné sur le dashboard
 * @param etatDashboard État des SA sélectionné par les boutons dans le dashboard
 * @param listSalleContainer Container pour l'affichage des salles
 */
function redirectionFromPlan (etatDashboard,listSalleContainer)
{
    setTimeout(() => {
        displaySalleByEtat(etatDashboard, listSalleContainer)
            .catch(error => console.error('Erreur lors de la récupération des salles :', error));
    }, 500);
}

/**
 * @author Victor
 * @brief Permet d'afficher les salles en fonction de l'état des SA sélectionné.
 * @param etat État des SA sélectionné pour la requête AJAX
 * @param listSalleContainer Container pour l'affichage des salles
 */
function displaySalleByEtat(etat, listSalleContainer)
{
    return fetch(`request/plan/findByEtat/${etat}`, {
        method:'GET',
        headers: {
            'X-Requested-With' : 'XMLHttpRequest' // REQUÊTE AJAX
        }
    })
        // Cas où la réponse n'est pas correcte
        .then(response => {
            if(!response.ok)
            {
                throw new Error(response.statusText);
            }
            return response.json();
        })
        .then(data => {
            listSalleContainer.innerHTML = ``;

            // Si aucune salle ne correspond
            if (data.length === 0)
            {
                const sallesContainer = document.createElement('div')
                sallesContainer.innerHTML =
                    `
                                <p>Aucun élément trouvé</p>
                            `
                listSalleContainer.appendChild(sallesContainer);
                return [] // Renvoie une liste vide si aucune salle
            }
            else
            {
                // Affiche toutes les salles qui ont au moins un sa correspondant à l'état
                data.forEach(salle => {
                    const sallesContainer = document.createElement('div');
                    sallesContainer.className = 'plan';

                    sallesContainer.innerHTML =
                        `
                            <div class="plan-voir-infos" id="${ salle.id }">
                                Détails
                            </div>
                            <div class="plan-main">
                                <div class="plan-main-left">
                                    <p class="plan-main-left-salle">SALLE <strong>${ salle.nom }</strong></p>
                                    <div class="plan-main-left-etage">
                                        <!-- image étages -->
                                        <svg viewBox="0 0 33 30" xmlns="http://www.w3.org/2000/svg">
                                            <path xmlns="http://www.w3.org/2000/svg" d="M14.9854 0.303252C15.9457 -0.101084 17.0543 -0.101084 18.0146 0.303252L32.1041 6.2218C32.652 6.45034 33 6.94843 33 7.49927C33 8.0501 32.652 8.5482 32.1041 8.77674L18.0146 14.6953C17.0543 15.0996 15.9457 15.0996 14.9854 14.6953L0.895899 8.77674C0.348047 8.54234 0 8.04424 0 7.49927C0 6.95429 0.348047 6.45034 0.895899 6.2218L14.9854 0.303252ZM28.6752 12.281L32.1041 13.7225C32.652 13.9511 33 14.4492 33 15C33 15.5508 32.652 16.0489 32.1041 16.2775L18.0146 22.196C17.0543 22.6004 15.9457 22.6004 14.9854 22.196L0.895899 16.2775C0.348047 16.0431 0 15.545 0 15C0 14.455 0.348047 13.9511 0.895899 13.7225L4.3248 12.281L14.1217 16.3947C15.6299 17.0275 17.3701 17.0275 18.8783 16.3947L28.6752 12.281ZM18.8783 23.8954L28.6752 19.7817L32.1041 21.2233C32.652 21.4518 33 21.9499 33 22.5007C33 23.0516 32.652 23.5497 32.1041 23.7782L18.0146 29.6967C17.0543 30.1011 15.9457 30.1011 14.9854 29.6967L0.895899 23.7782C0.348047 23.5438 0 23.0457 0 22.5007C0 21.9558 0.348047 21.4518 0.895899 21.2233L4.3248 19.7817L14.1217 23.8954C15.6299 24.5283 17.3701 24.5283 18.8783 23.8954Z"/>
                                        </svg>
                                        <p><strong>${ salle.etage }</strong></p>
                                    </div>
                                </div>
                                <div class="plan-separator"></div>
                                <div class="plan-main-right">
                                    <div class="plan-main-right-title">
                                        <p><strong>SA</strong> associés : </p>
                                    </div>
                                </div>
                            </div>
                        `;

                    // Ajout du bouton d'ajout de SA
                    if (rolesUtilisateur.includes('ROLE_CHARGEMISSION')) {
                        let titleContainer = sallesContainer.getElementsByClassName("plan-main-right-title")[0];
                        let addSAButton = document.createElement("button");
                        addSAButton.type = "button";
                        addSAButton.id = salle.id;
                        addSAButton.className = "plan-main-right-title-button";
                        addSAButton.innerHTML = '+';
                        titleContainer.appendChild(addSAButton);
                    }

                    listSalleContainer.appendChild(sallesContainer);

                    // Récupère la div qu'on vient de créer qui contiendra la liste des sa
                    const saContainerWrapper = sallesContainer.querySelector('.plan-main-right');

                    // Insère la liste des sa de la salle dans la div plan-main-right
                    getSABySalle(salle.id).then(saContainer => {
                        if (saContainer) 
                        {
                            saContainerWrapper.appendChild(saContainer);
                        }
                    }).catch(error => {
                        console.error('Erreur lors de la récupération des données SA :', error);
                    });

                    // Gère le click des boutons "voir infos"
                    const infosBouton = sallesContainer.querySelector('.plan-voir-infos');
                    infosBouton.addEventListener('click', () =>{
                        id = infosBouton.getAttribute('id');
                        window.location.href = `plan/modifier/${id}`;
                    });

                    // Gère le click des boutons ajouter un sa "+"
                    const addSAButton = sallesContainer.querySelector('.plan-main-right-title-button');
                    if (addSAButton) {
                        addSAButton.addEventListener('click', () =>{
                            id = addSAButton.getAttribute('id');
                            window.location.href = `plan/nouveau/${id}`;
                        });
                    }
                });
            }
        })
}

/**
 * @author Victor
 * @brief Permet d'afficher les salles en fonction du contenu de la barre de recherche
 * @param nom Nom rentré dans la barre de recherche par l'utilisateur
 * @param listSalleContainer Container pour l'affichage des salles
 */
function displaySalleBySearch(nom, listSalleContainer)
{
    return fetch(`request/plan/findBySalleOrSA/${nom}`, {
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
        .then(data => {
            listSalleContainer.innerHTML = ``;

            // Si aucune salle ne correspond
            if (data.length === 0)
            {
                const sallesContainer = document.createElement('div')
                sallesContainer.innerHTML =
                    `
                                <p>Aucun élément trouvé</p>
                            `
                listSalleContainer.appendChild(sallesContainer);
                return [] // Renvoie une liste vide si aucune salle
            }
            else
            {
                // Affiche toutes les salles qui ont au moins un sa correspondant à l'état
                data.forEach(salle => {
                    const sallesContainer = document.createElement('div');
                    sallesContainer.className = 'plan';

                    sallesContainer.innerHTML =
                        `
                            <div class="plan-voir-infos" id="${ salle.id }">
                                Détails
                            </div>
                            <div class="plan-main">
                                <div class="plan-main-left">
                                    <p class="plan-main-left-salle">SALLE <strong>${ salle.nom }</strong></p>
                                    <div class="plan-main-left-etage">
                                        <!-- image étages -->
                                        <svg viewBox="0 0 33 30" xmlns="http://www.w3.org/2000/svg">
                                            <path xmlns="http://www.w3.org/2000/svg" d="M14.9854 0.303252C15.9457 -0.101084 17.0543 -0.101084 18.0146 0.303252L32.1041 6.2218C32.652 6.45034 33 6.94843 33 7.49927C33 8.0501 32.652 8.5482 32.1041 8.77674L18.0146 14.6953C17.0543 15.0996 15.9457 15.0996 14.9854 14.6953L0.895899 8.77674C0.348047 8.54234 0 8.04424 0 7.49927C0 6.95429 0.348047 6.45034 0.895899 6.2218L14.9854 0.303252ZM28.6752 12.281L32.1041 13.7225C32.652 13.9511 33 14.4492 33 15C33 15.5508 32.652 16.0489 32.1041 16.2775L18.0146 22.196C17.0543 22.6004 15.9457 22.6004 14.9854 22.196L0.895899 16.2775C0.348047 16.0431 0 15.545 0 15C0 14.455 0.348047 13.9511 0.895899 13.7225L4.3248 12.281L14.1217 16.3947C15.6299 17.0275 17.3701 17.0275 18.8783 16.3947L28.6752 12.281ZM18.8783 23.8954L28.6752 19.7817L32.1041 21.2233C32.652 21.4518 33 21.9499 33 22.5007C33 23.0516 32.652 23.5497 32.1041 23.7782L18.0146 29.6967C17.0543 30.1011 15.9457 30.1011 14.9854 29.6967L0.895899 23.7782C0.348047 23.5438 0 23.0457 0 22.5007C0 21.9558 0.348047 21.4518 0.895899 21.2233L4.3248 19.7817L14.1217 23.8954C15.6299 24.5283 17.3701 24.5283 18.8783 23.8954Z"/>
                                        </svg>
                                        <p><strong>${ salle.etage }</strong></p>
                                    </div>
                                </div>
                                <div class="plan-separator"></div>
                                <div class="plan-main-right">
                                    <div class="plan-main-right-title">
                                        <p><strong>SA</strong> associés : </p>
                                    </div>
                                </div>
                            </div>
                        `;

                    // Ajout du bouton d'ajout de SA
                    if (rolesUtilisateur.includes('ROLE_CHARGEMISSION')) {
                        let titleContainer = sallesContainer.getElementsByClassName("plan-main-right-title")[0];
                        let addSAButton = document.createElement("button");
                        addSAButton.type = "button";
                        addSAButton.id = salle.id;
                        addSAButton.className = "plan-main-right-title-button";
                        addSAButton.innerHTML = '+';
                        titleContainer.appendChild(addSAButton);
                    }

                    listSalleContainer.appendChild(sallesContainer);

                    //récupère la div qu'on vient de créer qui contiendra la liste des sa
                    const saContainerWrapper = sallesContainer.querySelector('.plan-main-right');

                    // insère la liste des sa de la salle dans la div plan-main-right
                    getSABySalle(salle.id).then(saContainer => {
                        console.log(saContainer);
                        if (saContainer) 
                        {
                            saContainerWrapper.appendChild(saContainer);
                        } 
                        else 
                        {
                        }
                    }).catch(error => {
                        console.error('Erreur lors de la récupération des données SA :', error);
                    });

                    //gère le click des boutons "voir infos"
                    const infosBouton = sallesContainer.querySelector('.plan-voir-infos');
                    infosBouton.addEventListener('click', () =>{
                        id = infosBouton.getAttribute('id');
                        window.location.href = `plan/modifier/${id}`;
                    });

                    // Gère le click des boutons ajouter un sa "+"
                    const addSAButton = sallesContainer.querySelector('.plan-main-right-title-button');
                    if (addSAButton) {
                        addSAButton.addEventListener('click', () =>{
                            id = addSAButton.getAttribute('id');
                            window.location.href = `plan/nouveau/${id}`;
                        });
                    }
                })
            }
        })
}

/**
 * @author Victor
 * @brief Permet d'obtenir les SA en fonction d'une salle
 * @param idSalle Identifiant de la salle pour la requête AJAX
 *
 * @returns {HTMLDivElement} Élément div qui contient la liste des SA obtenus
 */
function getSABySalle(idSalle)
{
    return fetch(`request/plan/findBySalle/${idSalle}`, {
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
        .then(data => {
            if (data.length === 0)
            {
                return [] // Renvoie une liste vide si aucun sa
            }
            else
            {
                // liste qui contiendra les sa
                const plan_main_right_list = document.createElement('div');
                plan_main_right_list.className = 'plan-main-right-list';

                data.forEach(plan => {
                    // création d'un sa
                    const saContainer = document.createElement('div');
                    saContainer.className = 'plan-main-right-list-item';

                    saContainer.innerHTML =
                        `
                            <div class="plan-main-right-list-item-sa">
                                <div class="plan-main-right-etat ${ plan.etat }">
                                    <div class="plan-main-right-etat-popup ${ plan.etat }">
                                        <p>${ plan.etat }</p>
                                        <div class="plan-main-right-etat-popup-arrow ${ plan.etat }"></div>
                                    </div>
                                </div>
                                <!-- image capteur -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M176 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40c-35.3 0-64 28.7-64 64l-40 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l40 0 0 56-40 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l40 0 0 56-40 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l40 0c0 35.3 28.7 64 64 64l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40 56 0 0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40 56 0 0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40c35.3 0 64-28.7 64-64l40 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-40 0 0-56 40 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-40 0 0-56 40 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-40 0c0-35.3-28.7-64-64-64l0-40c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40-56 0 0-40c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40-56 0 0-40zM160 128l192 0c17.7 0 32 14.3 32 32l0 192c0 17.7-14.3 32-32 32l-192 0c-17.7 0-32-14.3-32-32l0-192c0-17.7 14.3-32 32-32zm192 32l-192 0 0 192 192 0 0-192z"/></svg>
                                <p class="plan-main-right-sa">SA ${ plan.nom }</p>
                            </div>
                            <button class="plan-main-right-btn" data-sa="${ plan.nom }">
                                <!-- image poubelle -->
                                <svg fill ="white" height="1.15vw"  http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z"/></svg>
                            </button>
                        `

                    // ajout du sa à la liste
                    plan_main_right_list.appendChild(saContainer);

                    if(data.length > 1)
                    {
                        plan_main_right_list.style.justifyContent = "flex-start";
                    }

                    // gestion de la suppression des plans
                    const deleteButton = saContainer.querySelector('.plan-main-right-btn');
                    const messageConfirmation = document.getElementById('message-confirmation');
                    deleteButton.addEventListener('click', () => {
                        messageConfirmation.style.display = 'block';
                        saNom = deleteButton.getAttribute('data-sa');
                        manageDeleteButton(saNom);
                    });
                });
                return plan_main_right_list;
            }
        })
}

/**
 * @author Victor
 * @brief Gère l'affichage du popup de suppression
 * @param saNom Nom du SA à supprimer
 */
function manageDeleteButton(saNom)
{
    const messageConfirmation = document.getElementById('message-confirmation');
    const btnAnnuler = document.getElementById('confirm-cancel-button');
    const btnValider = document.getElementById('confirm-delete-button');

    // cache la popup après séléction
    btnAnnuler.addEventListener('click', () => {
        messageConfirmation.style.display = 'none';
    });

    // valide et supprime le sa du plan
    btnValider.addEventListener('click', () => {
        messageConfirmation.style.display = 'none'; 
        deletePlan(saNom);
    });
}

/**
 * @author Victor
 * @brief Supprime l'association d'un sa à une salle
 * @param saNom Nom du SA
 */
function deletePlan (saNom){

    fetch(`/request/plan/supprimer/${saNom}`, {
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
            // Rafraîchis la page
            window.location.reload();
        })
        .catch(error => {
            console.log(error)
        })

}