document.getElementById('openModal').addEventListener('click', () => {
    document.getElementById('modalBackdrop').classList.remove('hidden');
    document.getElementById('customModal').classList.remove('hidden');
});

document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('modalBackdrop').classList.add('hidden');
    document.getElementById('customModal').classList.add('hidden');
});

/**
 * @author Côme
 * @brief Gère les évènements de clic sur les étages
 */
function handleEtageClicks()
{
    const etage = document.querySelectorAll('#bouton_etage');

    etage.forEach(etage => {
        const etageInfo = [
            etage.getAttribute('data-nom-etage'),
            etage.getAttribute('data-id-etage'),
        ];

        const deleteButton = etage.querySelector('.delete-btn-etage');

        // Ajout de l'évènement de clic sur le bouton 'Supprimer'
        deleteButton.addEventListener('click', () => {
            manageDeleteButton(etageInfo);
        });

        deleteButton.classList.add('active');
        deleteButton.classList.remove('inactive');

    });

}

/**
 * @author Axel
 * @brief Gère le bouton 'Supprimer' (son affichage et ses actions)
 * @param etageInfo Tableau des informations du bâtiment (son nom de bâtiment)
 */
function manageDeleteButton(etageInfo)
{
    const confirmPopup = document.getElementById('message-confirmation');
    const confirmCancelButton = document.getElementById('confirm-cancel-button');
    const confirmDeleteButton = document.getElementById('confirm-delete-button');
    confirmPopup.style.display = 'block';

    confirmCancelButton.addEventListener('click',  () => {
        console.log('test');
        confirmPopup.style.display = 'none';
    });

    // Confirmer la suppression
    confirmDeleteButton.addEventListener('click',  () => {
        deleteEtage(etageInfo);

        // Ferme le pop-up
        confirmPopup.style.display = 'none';
    });
}

/**
 * @author Côme
 * @brief Supprime l'étage passé en paramètre
 * @param etageInfo Informations de l'étage à supprimer
 */
function deleteEtage(etageInfo){
    const idEtage = etageInfo[1];

    fetch(`/request/etage/deleteEtage/${idEtage}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors de la suppression d'un étage: "+response.statusText);
            }
            return response.json();
        })
        .then(data => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

handleEtageClicks();
