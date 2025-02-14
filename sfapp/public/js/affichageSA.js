// Définition des variables pour le SA sélectionné et le précédent
let selectedSaId = null;
let previousItem = null;

// Dictionnaire permettant de convertir la valeur d'un état en son nom
const translateEtatValueToName = {
    "En stock": "EnStock",
    "À installer": "AInstaller",
    "Fonctionnel": "Fonctionnel",
    "Intervention nécessaire": "InterventionNecessaire"
}

document.addEventListener('DOMContentLoaded', function () {
    // Sélection des éléments de la page
    const SADetailsContainer = document.getElementById('sa-details');
    const SAItems = document.querySelectorAll('.sa');

    // Ajout des évènements des options de recherches
    handleSearchOptions();

    // Lorsque l'on clique sur un SA
    SAItems.forEach(item => {
        addSAClickEventListener(item)
    });
});

/**
 * @author Julien
 * @brief Gère les évènements des options de recherche de SA.
 */
function handleSearchOptions()
{
    // Récupération des éléments HTML nécessaires
    const searchBar = document.getElementById('search-bar');
    const etatInput = document.getElementById('list-etat');

    // Ajout de l'évènement de saisie de valeur dans la barre de recherche
    searchBar.addEventListener('input', () => {
        getSAByNameAndEtat(searchBar.value, etatInput.value)
    })

    // Ajout de l'évènement de changement de valeur dans le menu déroulant des états
    etatInput.addEventListener('change', () => {
        getSAByNameAndEtat(searchBar.value, etatInput.value)
    });
}

/**
 * @author Julien
 * @brief Récupère les SA par le nom et état recherché puis les affiche.
 * @param nom Nom du SA recherché
 * @param etat État du SA recherché
 */
function getSAByNameAndEtat(nom, etat)
{
    fetch(`/request/systeme_acquisition/getSAByNameAndEtat?nom=${nom}&etat=${etat}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        // Vérification du bon fonctionnement de la requête
        .then(response => {
            if(!response.ok)
            {
                throw new Error(response.statusText)
            }
            return response.json();
        })
        .then(data => {
            // Affiche les SA récupérés par la requête AJAX
            displaySA(data, document.getElementsByClassName('scroller-container')[0]);
        })
        .catch(error => {
            console.log(error)
        })
}

/**
 * @author Julien
 * @brief Ajoute les évènements de clic sur l'élément HTML du SA et ses boutons.
 * @param SAItem L'élément HTML du SA qui doit devenir cliquable
 */
function addSAClickEventListener(SAItem)
{
    // Récupère la div contenant les détails du SA
    const SADetailsContainer = document.getElementById('sa-details');

    // Ajout de l'évènement de clic sur l'élément HTML du SA
    SAItem.addEventListener('click', async () =>
    {
        // Affiche la div des détails du SA avec un scale pour un effet d'apparition
        SADetailsContainer.style.transform = 'scale(1)';

        // Récupération des données de l'élément sélectionné
        selectedSaId = SAItem.getAttribute('data-sa-id');

        // Récupération des attributs du SA
        const SAName = SAItem.getAttribute("data-sa-name");
        const SAId = SAItem.getAttribute("data-sa-id");
        const SASalleName = SAItem.getAttribute("data-sa-nomSalle");
        const SADateCrea = SAItem.getAttribute("data-sa-dateCrea");


        // Activation du bouton 'Modifier'
        const route = `/systeme_acquisition/modifier/${SAId}`;
        const editButton = SAItem.getElementsByClassName("edit-btn")[0];
        const deleteButton = SAItem.getElementsByClassName("delete-btn")[0];
        const separator = SAItem.getElementsByClassName('ligne-separatrice')[0];

        // Ajout de l'évènement de clic sur le bouton de modification
        editButton.addEventListener('click', () =>
        {
            window.location.href = route;
        });

        // Ajout de l'évènement de clic sur le bouton de suppression
        deleteButtonManagement(selectedSaId, deleteButton);

        // Désélectionne le SA précédemment sélectionné si existant
        if (previousItem) {
            // Récupère les éléments de l'ancien SA
            const editButton = previousItem.getElementsByClassName("edit-btn")[0];
            const deleteButton = previousItem.getElementsByClassName("delete-btn")[0];
            const separator = previousItem.getElementsByClassName('ligne-separatrice')[0];

            // Réinitialise les couleurs
            previousItem.style.backgroundColor = SAItem.style.backgroundColor;
            previousItem.style.color = SAItem.style.color;

            // Cache les boutons de suppression et modification
            editButton.classList.remove('active')
            editButton.classList.add('inactive');
            deleteButton.classList.remove('active')
            deleteButton.classList.add('inactive');

            // Modifie le style de la barre de séparation
            separator.style.borderLeft = 'black solid 0.15vw';
        }

        // Change la couleur de fond et du texte de l'élément HTML du SA
        previousItem = SAItem;
        SAItem.style.backgroundColor = '#666666';
        SAItem.style.color = '#FFFFFF';

        // Affiche les boutons de suppression et modification
        editButton.classList.remove('inactive')
        editButton.classList.add('active');
        deleteButton.classList.remove('inactive')
        deleteButton.classList.add('active');

        // Modifie le style de la barre de séparation
        separator.style.borderLeft = '#CACACA solid 0.15vw';

        // Remplace le contenu de la div des détails du SA
        SADetailsContainer.innerHTML =
            `
                <div class="page-capteurs-header">
                    <h2>SA ${SAName}</h2>
                </div>
                <div class="infos">
                    <p>SA créé le : <span><b>${SADateCrea}</b></span></p>
                </div>
            `;

        // Ajoute le nom de la salle associée si existant
        if (SASalleName.length !== 0) {
            SADetailsContainer.getElementsByClassName("page-capteurs-header")[0].innerHTML += `<p>SALLE ${SASalleName}</p>`;
        }
    });
}

/**
 * @author Axel
 * @brief Gère l'évènement de clic sur un bouton de suppression. Utilise une requête AJAX pour savoir
 *        si le SA est associé à une salle ou non puis appelle la fonction pour supprimer le SA.
 * @param idSA Identifiant du SA à supprimer
 * @param deleteButton Bouton 'Supprimer' du SA
 */
function deleteButtonManagement(idSA, deleteButton)
{
    const confirmPopup = document.getElementById('message-confirmation');
    const confirmCancelButton = document.getElementById('confirm-cancel-button');
    const confirmDeleteButton = document.getElementById('confirm-delete-button');

    // Ajoute l'évènement de clic sur le bouton de suppression
    deleteButton.addEventListener('click', () => {
        // Appel de la requête AJAX pour savoir si le SA a une salle
        fetch(`request/systeme_acquisition/getSalleBySA/${idSA}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            // Cas où la requête ne fonctionne pas
            .then(response => {
                if(!response.ok)
                {
                    throw new Error(response.statusText)
                }
                return response.json();
            })
            .then(data => {
                // Vérifie si le SA a une salle
                if(data.length === 0)
                {
                    // Supprime directement le SA
                    deleteSA(idSA, false)
                }
                else
                {
                    // Affiche la popup de confirmation
                    confirmPopup.style.display = 'block';
                }
            })

    });

    // Ajout de l'évènement de clic sur le bouton 'Annuler' du popup
    confirmCancelButton.addEventListener('click', () => {
        // Ferme le popup
        confirmPopup.style.display = 'none';
    });

    // Ajout de l'évènement de clic sur le bouton 'Confirmer' du popup
    confirmDeleteButton.addEventListener('click', () => {
        // Supprime le SA
        deleteSA(idSA, true)

        // Ferme le popup
        confirmPopup.style.display = 'none';
    });
}

/**
 * @author Axel, Corentin
 * @brief Appel une requête AJAX afin de supprimer le SA demandé.
 * @param id Identifiant du SA à supprimer
 * @param isSalleWithSA Booléen permettant de savoir si le SA est associé à une salle ou non
 */
function deleteSA(id, isSalleWithSA)
{
    fetch(`request/systeme_acquisition/deleteSa/${id}/${isSalleWithSA}`, {
        method:'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        // Cas où la réponse n'est pas bonne
        .then(response =>
        {
            if(!response.ok)
            {
                throw new Error(response.statusText);
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

/**
 * @author Axel, Julien
 * @brief Affiche les SA correspondant à la recherche
 * @param listSA Liste des SA à afficher.
 * @param listSAContainer L'élément HTML contenant la liste des SA
 */
function displaySA(listSA, listSAContainer) {
    // Vide le container regroupant tous les SA affiché
    listSAContainer.innerHTML = ``;

    // Vérifie s'il y a des SA à afficher
    if (listSA.length !== 0)
    {
        listSA.forEach(SA => {
            // Créé un élément 'div' avec les attributs du SA
            let SAContainer = document.createElement('div');
            SAContainer.className = 'sa';
            SAContainer.setAttribute('data-sa-name', SA.nom);
            SAContainer.setAttribute('data-sa-id', SA.id);
            SAContainer.setAttribute('data-sa-nomSalle', (SA.nomSalle)?SA.nomSalle:"");
            SAContainer.setAttribute('data-sa-dateCrea', convertDateToString(SA.dateCreation));
            SAContainer.setAttribute('data-sa-assossie', SA.assossie);

            // Insère le code HTML pour l'affichage du SA dans la div
            SAContainer.innerHTML += `
                        <div class="sa-container-infos">
                            <div class="etat-rect ${translateEtatValueToName[SA.etat]}">
                                <div class="etat-message">${SA.etat}</div>
                            </div>
                            <p>SA ${SA.nom}</p>
                            <div class="ligne-separatrice"></div>
                            <p class="sa-salle-attribue"></p>
                        </div>
                        <div class ="buttons-display">
                                <button class="button-type3 button-cancel delete-btn delete admin inactive">
                                    <p class="txt">SUPPRIMER</p>
                                    <div class="svg">
                                        <svg fill ="white" height="1.15vw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z"/></svg>
                                    </div>
                                </button>
                                <button class="button-type3 button-confirm edit-btn modif admin inactive">
                                    <p class="txt">MODIFIER</p>
                                    <div class="svg">
                                        <svg fill ="white" height="1.15vw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/></svg>
                                    </div>
                                </button>
                            </div>
                    `;
            // Récupère dans la div l'élément où est indiqué la salle attribuée au SA
            let SalleAttribueContainer = SAContainer.getElementsByClassName('sa-salle-attribue')[0];
            // Vérifie si le SA a une salle associée (si le nom de la salle n'est pas null et que le plan n'a pas de date de disassociation)
            if (SA.nomSalle && !SA.assossie)
            {
                SalleAttribueContainer.innerHTML = `SALLE ATTRIBUÉ :&nbsp;<b>${SA.nomSalle}</b>`;
            }
            else
            {
                SalleAttribueContainer.innerHTML = `SALLE ATTRIBUÉ :&nbsp;<b>AUCUNE</b>`;
            }

            // Ajoute la div au container pour afficher le SA
            listSAContainer.appendChild(SAContainer);

            // Ajoute les évènements de clic sur le SA
            addSAClickEventListener(SAContainer);
        });
    } else {
        // Créé une div affichant qu'il n'y a pas de SA correspondant
        const displayDiv = document.createElement('div');
        displayDiv.innerHTML =
            `
                Aucun SA trouvé
            `

        // Ajoute la div au container des SA
        listSAContainer.appendChild(displayDiv);
    }
}

/**
 * @author Julien
 * @brief Converti une date obtenue dans la base de données en chaîne de caractères au format "JJ/MM/AAAA".
 * @param dateString Chaîne de caractères renvoyée par la base de données
 * @returns {string} Chaîne de caractères au format "JJ/MM/AAAA"
 */
function convertDateToString(dateString) {
    // Crée un objet Date à partir de la chaîne de caractères de la BD
    const date = new Date(dateString.date);

    // Converti chaque valeur en string (en gardant un 0 si besoin pour le jour et le mois)
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    // Combine le tout en JOUR/MOIS/ANNÉE
    return `${day}/${month}/${year}`;
}
