// Ajout de l'évènement au chargement de la page
document.addEventListener('DOMContentLoaded', function ()
{
    // Gestion de la modification de l'état des SA dans la page détaillée des plans
    const forms = document.querySelectorAll('.etatForm');
    let selectList = []; // Liste des éléments select des formulaires
    let formsList = []; // Liste des formulaires
    forms.forEach(form => {
        let select;
        if (form.className !== "") {
            /* Pour éviter de sélectionner les div des formulaires,
            car Symfony donne le même nom de class au fils des form */
            form.firstElementChild.className = "";
            // On récupère les éléments select
            select = form.querySelector('#change_etat_Etat');
            // On modifie leur classe pour la couleur
            select.className = select.value;
            // On leur attribue un data pour les distinguer
            select.setAttribute('data-sa-nom', form.getAttribute('data'));
            // On lie les select avec leur form
            formsList[select.getAttribute('data-sa-nom')] = form;
            selectList.push(select);
        }
    });


    // Modifie les états quand on change d'option
    selectList.forEach(select => {
        select.addEventListener('change', () => {
            //on recup l'element du form
            nomSA = select.getAttribute('data-sa-nom');
            //on sauvegarde le nom du sa modififé dans un attribut du form caché
            hiddenField = formsList[nomSA].querySelector('#change_etat_isSubmit');
            hiddenField.value = nomSA;
            formsList[nomSA].submit();
        });
    });

    // Gestion de la suppression des plans
    const deleteButtons = document.querySelectorAll('.form-right-sa-list-item-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            messageConfirmation.style.display = 'block';
            saNom = button.getAttribute('data-sa');
        });
    });

    const messageConfirmation = document.getElementById('message-confirmation');
    const btnAnnuler = document.getElementById('confirm-cancel-button');
    const btnValider = document.getElementById('confirm-delete-button');

    // Ajout de l'évènement de clic sur le bouton 'Annuler'
    btnAnnuler.addEventListener('click', () => {
        messageConfirmation.style.display = 'none';
    });

    // Ajout de l'évènement de clic sur le bouton 'Valider'
    btnValider.addEventListener('click', () => {
        messageConfirmation.style.display = 'none';
        deletePlan(saNom);
    });

    const listNoteContainer = document.getElementById('liste-notes');
    // Récupère l'état sélectionné
    const type = document.getElementById('list-type');

    const salle = listNoteContainer.getAttribute('data-salle-id');
    // Séléctionne par défaut toute les salles (état : "tout")
    type.value = type.options[0].value;

    // Affichage des salles en fonction de l'état séléctionné
    displayNoteByType(type.value, salle, listNoteContainer);

    // Actualise la liste des salles à chaque changement d'étages
    type.addEventListener('change', () =>
    {
        displayNoteByType(type.value, salle, listNoteContainer);
    })
});

/**
 * @author Côme
 * @brief Permet de générer le HTML pour une note de type problème.
 * @param note Objet représentant une note
 * @returns {string} HTML de la note
 */
function generateProblemeHTML(note) {
    const url = `/plan/problemelu/${note.id}`;
    return `
        <div class="note_probleme">
            <div class="header_note_probleme">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier"> 
                        <path d="M12 15H12.01M12 12V9M4.98207 19H19.0179C20.5615 19 21.5233 17.3256 20.7455 15.9923L13.7276 3.96153C12.9558 2.63852 11.0442 2.63852 10.2724 3.96153L3.25452 15.9923C2.47675 17.3256 3.43849 19 4.98207 19Z" stroke-linecap="round"></path> 
                    </g>
                </svg>
                ${note.titre}
                <a class="marquelu_note" href="${url}">Marquer comme lu</a>
            </div>
            <div class="text_note_probleme">${note.texte}</div>
        </div>
    `;
}

/**
 * @brief Génère le HTML pour une note standard.
 * @param note Objet représentant une note
 * @returns {string} HTML de la note
 */
function generateStandardNoteHTML(note) {
    console.log(note)
    let iconHTML = '';
    if (note.type === 'ProblemeLu') {
        iconHTML = `
            <svg class="svg_ProblemeLu" viewBox="0 0 25 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.2124 0L8.00954 14.6252L1.78592 8.63662L0 10.358L8.00954 18.0639L25 1.71871L23.2124 0Z"/>
            </svg>
        `;
    } else {
        iconHTML = `
            <svg class="svg_Information" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13 19.9991C12.9051 20 12.7986 20 12.677 20H7.19691C6.07899 20 5.5192 20 5.0918 19.7822C4.71547 19.5905 4.40973 19.2842 4.21799 18.9079C4 18.4801 4 17.9203 4 16.8002V7.2002C4 6.08009 4 5.51962 4.21799 5.0918C4.40973 4.71547 4.71547 4.40973 5.0918 4.21799C5.51962 4 6.08009 4 7.2002 4H16.8002C17.9203 4 18.4796 4 18.9074 4.21799C19.2837 4.40973 19.5905 4.71547 19.7822 5.0918C20 5.5192 20 6.07899 20 7.19691V12.6747C20 12.7973 20 12.9045 19.9991 13M13 19.9991C13.2857 19.9966 13.4663 19.9862 13.6388 19.9448C13.8429 19.8958 14.0379 19.8147 14.2168 19.705C14.4186 19.5814 14.5916 19.4089 14.9375 19.063L19.063 14.9375C19.4089 14.5916 19.5809 14.4186 19.7046 14.2168C19.8142 14.0379 19.8953 13.8424 19.9443 13.6384C19.9857 13.4659 19.9964 13.2855 19.9991 13M13 19.9991V14.6001C13 14.04 13 13.7598 13.109 13.5459C13.2049 13.3577 13.3577 13.2049 13.5459 13.109C13.7598 13 14.0396 13 14.5996 13H19.9991"></path> 
            </svg>
        `;
    }
    return `
        <div class="note">
            ${iconHTML}
            <div class="info_note">
                <div class="header_note">${note.titre}</div>
                <div class="text_note">${note.texte}</div>
            </div>
            <div class="date_note">${note.date}</div>
        </div>
    `;
}

/**
 * @brief Permet d'afficher les notes en fonction du type sélectionné.
 */
function displayNoteByType(type, salle, listNoteContainer) {
    return fetch(`/request/plan/findByType/${type}/${salle}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest' // Requête AJAX
        }
    })
        .then(response => {
            if (!response.ok) {
                throw Error(response.statusText);
            }
            return response.json();
        })
        .then(data => {
            listNoteContainer.innerHTML = '';

            if (data.length === 0) {
                const notesContainer = document.createElement('div');
                notesContainer.innerHTML = '<p>Aucun élément trouvé</p>';
                listNoteContainer.appendChild(notesContainer);
                return [];
            } else {
                data.forEach(note => {
                    const notesContainer = document.createElement('div');
                    if (note.type === 'Probleme') {
                        notesContainer.innerHTML = generateProblemeHTML(note);
                    }
                    listNoteContainer.appendChild(notesContainer);
                });
                data.forEach(note => {
                    const notesContainer = document.createElement('div');
                    if (note.type !== 'Probleme') {
                        notesContainer.innerHTML = generateStandardNoteHTML(note);
                    }
                    listNoteContainer.appendChild(notesContainer);
                });
            }
        });
}



/**
 * @author Victor
 * @brief Supprime l'association d'un sa à une salle
 * @param saNom Nom du sa
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
            window.location.reload();
        })
        .catch(error => {
            console.log(error)
        })

}