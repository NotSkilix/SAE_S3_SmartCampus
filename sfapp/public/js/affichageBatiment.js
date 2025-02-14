// Récupérer les éléments du DOM
const openModalButton = document.getElementById('openModal');
const closeModalButton = document.getElementById('closeModal');
const modal = document.getElementById('customModal');
const backdrop = document.getElementById('modalBackdrop');

/**
 * @author Côme
 * @brief Ouvre le modal.
 */
function openModal() {
    modal.classList.remove('hidden');
    backdrop.classList.remove('hidden');
}

/**
 * @author Côme
 * @brief Ferme le modal.
 */
function closeModal() {
    modal.classList.add('hidden');
    backdrop.classList.add('hidden');
}

/**
 * @author Côme
 * @brief Ajout des évènements de clic sur les bâtiments.
 */
function handleBatimentClicks()
{
    if (openModalButton) {
        // Ajout des évènements de clic sur les boutons du modal
        openModalButton.addEventListener('click', openModal);
        closeModalButton.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);

        document.getElementById('customModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalContent').innerHTML = ''; // Supprime le contenu
        });
    }

    // Récupère les éléments HTML des bâtiments
    const batiments = document.querySelectorAll('#batiment');
    const lienBatiment= document.querySelectorAll('.lien-batiment');

    lienBatiment.forEach(lienBatiment => {
        // Récupère le nom du bâtiment
        const nomBatiment = lienBatiment.getAttribute('data-nom-batiment');

        // Ajout de l'évènement de clic sur un bâtiment pour changer de bâtiment sélectionné
        lienBatiment.addEventListener('click', () => {
            fetch(`request/batiment_selectioné/${nomBatiment}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
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
        })


    })

    batiments.forEach(batiments => {
        // Récupère les attributs du bâtiment
        const batimentInfo = [
            batiments.getAttribute('data-nom-batiment'),
            batiments.getAttribute('data-id-batiment'),
        ]

        const route = `/accueil/modifier/${batimentInfo[1]}`;
        const deleteButton = batiments.querySelector('.delete-btn-batiment');
        const editButton = batiments.querySelector('.edit-btn-batiment');

        // Ajout de l'évènement de clic sur le bouton de suppression
        deleteButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            manageDeleteButton(batimentInfo);
        });
        // Ajout de l'évènement de clic sur le bouton de modification
        editButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            window.location.href = route;
        })

        // Affiche le bouton 'Supprimer'
        deleteButton.classList.add('active');
        deleteButton.classList.remove('inactive');

        // Affiche le bouton 'Modifier'
        editButton.classList.add('active');
        editButton.classList.remove('inactive');
    });

}

/**
 * @author Côme
 * @brief Gestion du bouton 'Supprimer' (son affichage et ses actions)
 * @param batimentInfo Tableau des informations du bâtiment (son nom de bâtiment)
 */
function manageDeleteButton(batimentInfo)
{
    const confirmPopup = document.getElementById('message-confirmation');
    const confirmCancelButton = document.getElementById('confirm-cancel-button');
    const confirmDeleteButton = document.getElementById('confirm-delete-button');

    // Affiche la popup de confirmation
    confirmPopup.style.display = 'block';

    // Ajout de l'évènement de clic du bouton d'annulation du popup
    confirmCancelButton.addEventListener('click',  () => {
        confirmPopup.style.display = 'none';
    });

    // Ajout de l'évènement de clic du bouton de confirmation du popup
    confirmDeleteButton.addEventListener('click',  () => {
        // Suppression du bâtiment
        deleteBatiment(batimentInfo);

        // Ferme le popup
        confirmPopup.style.display = 'none';
    });

}

/**
 * @author Côme
 * @brief Supprime le bâtiment ainsi que ses étages et ses salles de la BD
 * @param batimentInfo Informations sur le bâtiment à supprimer
 */
function deleteBatiment(batimentInfo){

    const nomBatiment = batimentInfo[0];
    fetch(`request/batiment/deleteBatiment/${nomBatiment}`, {
        method: 'DELETE',
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
            window.location.reload();
        })
        .catch(error => {
            console.log(error)
        })
}

/**
 * @author Côme
 * @brief Récupère les bâtiments avec un nom similaire à la barre de recherche et
 *        les affiches en appelant la fonction.
 * @param batimentName Nom du bâtiment recherché
 */
function getBatimentByNameAndDisplay(batimentName)
{

    fetch(`request/accueil/getBatimentByName/${batimentName}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if(!response.ok)
            {
                // Cas où la réponse n'est pas correcte
                throw new Error("Erreur lors de la récupération du nom du bâtiment: " + response.statusText)
            }
            return response.json();
        })
        .then(data => {
            // Affiche les bâtiments
            displayBatiment(data.batiment);
        })
}


/**
 * @author Côme
 * @brief Récupère tous les bâtiments et appelle la fonction d'affichage
 */
function getAllBatimentAndDisplay()
{
    fetch(`request/accueil/getAllBatimentsWithDetails`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if(!response.ok)
            {
                // Cas où la réponse n'est pas correcte
                throw new Error("Erreur pour récupérer tout les bâtiments: " + response.statusText)
            }

            return response.json();
        })
        .then(data => {
            // Affiche les bâtiments
            displayBatiment(data.batiments);
        })
}

/**
 * @author Côme
 * @brief Affiche les bâtiments donnés en paramètres de fonction
 * @param listBatiment Liste des bâtiments
 */
function displayBatiment(listBatiment)
{
    const batimentDiv = document.getElementById('batiment-div');
    batimentDiv.innerHTML= '';

    // Si la réponse est supérieur à 0
    if(listBatiment.length > 0)
    {
        listBatiment.forEach(batiment => {
            const batimentText = document.createElement("div");

            // Pour la température
            let moyenneTemp = "?";
            if(batiment.moyenneTemp > 0)
            {
                moyenneTemp = batiment.moyenneTemp.toFixed(1);
            }

            batimentText.innerHTML =
                `
                   <div class="lien-batiment"
                        data-nom-batiment="${batiment.nom}">
                        <div class="batiments" id="bat" data-id-batiment="${ batiment.id }" >
                            <div class="batiment_top">
                                <p class="batiment">
                                    <svg viewBox="0 0 31 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.875 0C1.73568 0 0 1.46973 0 3.28125V31.7188C0 33.5303 1.73568 35 3.875 35H11.625V29.5312C11.625 27.7197 13.3607 26.25 15.5 26.25C17.6393 26.25 19.375 27.7197 19.375 29.5312V35H27.125C29.2643 35 31 33.5303 31 31.7188V3.28125C31 1.46973 29.2643 0 27.125 0H3.875ZM5.16667 16.4062C5.16667 15.8047 5.74792 15.3125 6.45833 15.3125H9.04167C9.75208 15.3125 10.3333 15.8047 10.3333 16.4062V18.5938C10.3333 19.1953 9.75208 19.6875 9.04167 19.6875H6.45833C5.74792 19.6875 5.16667 19.1953 5.16667 18.5938V16.4062ZM14.2083 15.3125H16.7917C17.5021 15.3125 18.0833 15.8047 18.0833 16.4062V18.5938C18.0833 19.1953 17.5021 19.6875 16.7917 19.6875H14.2083C13.4979 19.6875 12.9167 19.1953 12.9167 18.5938V16.4062C12.9167 15.8047 13.4979 15.3125 14.2083 15.3125ZM20.6667 16.4062C20.6667 15.8047 21.2479 15.3125 21.9583 15.3125H24.5417C25.2521 15.3125 25.8333 15.8047 25.8333 16.4062V18.5938C25.8333 19.1953 25.2521 19.6875 24.5417 19.6875H21.9583C21.2479 19.6875 20.6667 19.1953 20.6667 18.5938V16.4062ZM6.45833 6.5625H9.04167C9.75208 6.5625 10.3333 7.05469 10.3333 7.65625V9.84375C10.3333 10.4453 9.75208 10.9375 9.04167 10.9375H6.45833C5.74792 10.9375 5.16667 10.4453 5.16667 9.84375V7.65625C5.16667 7.05469 5.74792 6.5625 6.45833 6.5625ZM12.9167 7.65625C12.9167 7.05469 13.4979 6.5625 14.2083 6.5625H16.7917C17.5021 6.5625 18.0833 7.05469 18.0833 7.65625V9.84375C18.0833 10.4453 17.5021 10.9375 16.7917 10.9375H14.2083C13.4979 10.9375 12.9167 10.4453 12.9167 9.84375V7.65625ZM21.9583 6.5625H24.5417C25.2521 6.5625 25.8333 7.05469 25.8333 7.65625V9.84375C25.8333 10.4453 25.2521 10.9375 24.5417 10.9375H21.9583C21.2479 10.9375 20.6667 10.4453 20.6667 9.84375V7.65625C20.6667 7.05469 21.2479 6.5625 21.9583 6.5625Z"
                                              fill="#000000"/>
                                    </svg>
                                    BATIMENT : <b>${batiment.nom }</b>
                                </p>
                            </div>
                            <div class="batiment_bottom">
                                <div class="nb_etage">
                                    <svg viewBox="0 0 33 30" xmlns="http://www.w3.org/2000/svg">
                                        <path xmlns="http://www.w3.org/2000/svg" d="M14.9854 0.303252C15.9457 -0.101084 17.0543 -0.101084 18.0146 0.303252L32.1041 6.2218C32.652 6.45034 33 6.94843 33 7.49927C33 8.0501 32.652 8.5482 32.1041 8.77674L18.0146 14.6953C17.0543 15.0996 15.9457 15.0996 14.9854 14.6953L0.895899 8.77674C0.348047 8.54234 0 8.04424 0 7.49927C0 6.95429 0.348047 6.45034 0.895899 6.2218L14.9854 0.303252ZM28.6752 12.281L32.1041 13.7225C32.652 13.9511 33 14.4492 33 15C33 15.5508 32.652 16.0489 32.1041 16.2775L18.0146 22.196C17.0543 22.6004 15.9457 22.6004 14.9854 22.196L0.895899 16.2775C0.348047 16.0431 0 15.545 0 15C0 14.455 0.348047 13.9511 0.895899 13.7225L4.3248 12.281L14.1217 16.3947C15.6299 17.0275 17.3701 17.0275 18.8783 16.3947L28.6752 12.281ZM18.8783 23.8954L28.6752 19.7817L32.1041 21.2233C32.652 21.4518 33 21.9499 33 22.5007C33 23.0516 32.652 23.5497 32.1041 23.7782L18.0146 29.6967C17.0543 30.1011 15.9457 30.1011 14.9854 29.6967L0.895899 23.7782C0.348047 23.5438 0 23.0457 0 22.5007C0 21.9558 0.348047 21.4518 0.895899 21.2233L4.3248 19.7817L14.1217 23.8954C15.6299 24.5283 17.3701 24.5283 18.8783 23.8954Z"/>
                                    </svg>
                                    <p>${ batiment.nbEtages } Etages</p>
                                </div>
                                <div class="nb_salle">
                                    <svg viewBox="0 0 35 29" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 2.41667C0 1.07995 1.11719 0 2.5 0H32.5C33.8828 0 35 1.07995 35 2.41667C35 3.75339 33.8828 4.83333 32.5 4.83333H2.5C1.11719 4.83333 0 3.75339 0 2.41667ZM0 14.5C0 13.1633 1.11719 12.0833 2.5 12.0833H32.5C33.8828 12.0833 35 13.1633 35 14.5C35 15.8367 33.8828 16.9167 32.5 16.9167H2.5C1.11719 16.9167 0 15.8367 0 14.5ZM35 26.5833C35 27.9201 33.8828 29 32.5 29H2.5C1.11719 29 0 27.9201 0 26.5833C0 25.2466 1.11719 24.1667 2.5 24.1667H32.5C33.8828 24.1667 35 25.2466 35 26.5833Z"/>
                                    </svg>
                                    <p>${ batiment.nbSalles } Salles</p>
                                </div>
                                <div class="température">
                                    <svg viewBox="0 0 23 38" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.5 4.75093C9.38368 4.75093 7.66667 6.34694 7.66667 8.31412V20.5255C7.66667 21.8097 7.09965 22.8935 6.44479 23.6804C5.60625 24.69 5.11111 25.9445 5.11111 27.3178C5.11111 30.5989 7.97014 33.2565 11.5 33.2565C15.0299 33.2565 17.8889 30.5989 17.8889 27.3178C17.8889 25.9445 17.3938 24.69 16.5552 23.6878C15.9003 22.901 15.3333 21.8172 15.3333 20.5329V8.31412C15.3333 6.34694 13.6163 4.75093 11.5 4.75093ZM2.55556 8.31412C2.55556 3.72651 6.5566 0 11.5 0C16.4434 0 20.4444 3.71909 20.4444 8.31412V20.5181C20.4444 20.5255 20.4524 20.5403 20.4604 20.5626C20.4764 20.6071 20.5243 20.6814 20.5962 20.7705C22.1056 22.5818 23 24.8533 23 27.3104C23 33.212 17.849 38 11.5 38C5.15104 38 0 33.2194 0 27.3178C0 24.8533 0.894444 22.5818 2.40382 20.7779C2.47569 20.6888 2.52361 20.6146 2.53958 20.57C2.54757 20.5478 2.55556 20.5329 2.55556 20.5255V8.31412ZM15.3333 27.3178C15.3333 29.285 13.6163 30.881 11.5 30.881C9.38368 30.881 7.66667 29.285 7.66667 27.3178C7.66667 25.7664 8.73681 24.445 10.2222 23.9551V15.4405C10.2222 14.7873 10.7972 14.2528 11.5 14.2528C12.2028 14.2528 12.7778 14.7873 12.7778 15.4405V23.9551C14.2632 24.445 15.3333 25.7664 15.3333 27.3178Z"/>
                                    </svg>
                                    <p id="avg-temp">${moyenneTemp}°C</p>
                                    
                                    <div class="message_temp">Température moyenne du batiment</div>
                                </div>
                            </div>
                        </div>
                   </div> 
                `;

            batimentDiv.appendChild(batimentText)

            handleBatimentClicks();
        })
    }
    else
    {
        const batimentText = document.createElement("div");

        batimentText.innerHTML =
            `
                <h3> Aucun bâtiment avec ce nom... </h3>
            `
        ;

        batimentDiv.appendChild(batimentText);
    }
}

/**
 * @author Côme
 * @brief Gére le fonctionnement de la barre de recherche grâce aux appels de fonctions
 */
function manageSearchBar()
{
    const searchBar = document.getElementById('search-bar');

    // Nettoie la search bar
    searchBar.value = '';

    // Ajout de l'évènement de saisie de valeur dans la barre de recherche
    searchBar.addEventListener('input', () =>
    {
        // Affiche la recherche la barre de recherche n'est pas vide
        if(searchBar.value.length !== 0)
        {
            getBatimentByNameAndDisplay(searchBar.value)
        }
        else
        {
            getAllBatimentAndDisplay();
        }
    })
}

manageSearchBar();
handleBatimentClicks();
