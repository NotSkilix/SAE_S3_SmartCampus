// Ajout de l'évènement au chargement de la page
document.addEventListener('DOMContentLoaded', function(){
    const buttons = document.querySelectorAll('.page-button');

    manageRedirectionBatiment();
    manageRedirectionPlanInstall(buttons);
    addDate();
    updateBatimentScore();
    manageNotesActions();
})
/**
 * @author Corentin
 * @brief Permet d'avoir une redirection vers le batiment associé
 */
function manageRedirectionBatiment()
{
    const redirectionButton = document.querySelectorAll('.page-batiment-button'); // bouton de redirection
    redirectionButton.forEach(button => {
        button.addEventListener('click', () => {
            window.location.href = `/`;
        });
    });
}

/**
 * @author Corentin
 * @brief Permet d'avoir une redirection vers les plans a installer
 * @param buttons Bouttons qui permet la redirection vers la page plan avec le tri.
 */
function manageRedirectionPlanInstall(buttons)
{
    buttons.forEach(button => {
        button.addEventListener('click', (event) => {
            const etat = button.getAttribute('data-etat');
            localStorage.setItem('etat', etat); // Stocke l'état dans le localStorage
            window.location.href = '/plan';
        });
    });
}

/**
 * @author Corentin
 * @brief Permet d'avoir la date du jour
 */
function addDate()
{
    const date = document.getElementById('date');
    const contentDate = new Date();
    const dayNames = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
    const monthNames = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
    date.innerText = `${dayNames[contentDate.getDay()]} ${contentDate.getDate()} ${monthNames[contentDate.getMonth()]} ${contentDate.getFullYear()}`;
}

/**
 * @author Julien
 * @brief Met à jour le score global de confort environnemental du bâtiment.
 * @returns {Promise<void>}
 */
async function updateBatimentScore() {
    const score = await getBatimentScore();
    document.getElementById('score-container').innerHTML = (score) ? `${Math.round(score)}%` : '?';
}

/**
 * @author Axel
 * @brief Gére les actions en rapport avec les notes pour le chargé de mission.
 *        Contient la transmission d'une note, la modification de son conseil et si l'on souhaite l'ignorer

 */
function manageNotesActions()
{
    const listButtonSendNote = document.querySelectorAll('.button-send-note'); // Les boutons permettant le transfer au technicien
    const listButtonEditNote = document.querySelectorAll('.button-edit-note'); // Les boutons permettant de modifier le conseil d'une note
    const listButtonIgnoreNote = document.querySelectorAll('.button-ignore-note'); // Les boutons permettant d'ignorer une note


    /** Transmettre la note*/
    // Rend le texte de transmission d'une note cliquable
    listButtonSendNote.forEach(button =>
    {
        button.addEventListener('click', () =>
        {
            const noteDiv = button.parentElement; // Récupère le parent pour avoir l'id de la note
            const noteId = noteDiv.getAttribute('data-note-id');

            sendNote(noteId);
        })
    })

    /** Modifier la note*/
    // Rend le texte de modification d'une note cliquable
    // listButtonEditNote.forEach(button =>
    // {
    //     button.addEventListener('click', () =>
    //     {
    //         const noteDiv = button.parentElement; // Récupère le parent pour avoir l'id de la note
    //         const noteId = noteDiv.getAttribute('data-note-id');
    //
    //
    //     })
    // })

    /** Ignorer la note */
    // Rend le texte pour ignorer d'une note cliquable
    listButtonIgnoreNote.forEach(button =>
    {
        button.addEventListener('click', () =>
        {
            console.log(button);

            const noteDiv = button.parentElement; // Récupère le parent pour avoir l'id de la note
            const noteId = noteDiv.getAttribute('data-note-id');

            ignoreNote(noteId);
        })
    })
}

/**
 * @author Axel
 * @brief Fonction permettant l'envoie de la note au technicien
 * @param idNote l'id de la note à envoyer
 */
function sendNote(idNote)
{
    idNote = parseInt(idNote) // Transforme de string à int

    fetch(`/request/dashboard/sendNote/${idNote}`, {
        method: 'GET',
        headers:
        {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response =>
    {
        if (!response.ok)
        {
            throw new Error(response.statusText);
        }

        return response.json();
    })
    .then(data =>
    {
        window.location.reload();
    })
        .catch(error =>
        {
            console.log(error);
        })
}

/**
 * @author Axel
 * @brief Fonction permettant de modifier le conseil d'une note
 * @param idNote l'id de la note à modifier
 */
function editNote(idNote)
{
    console.log()
}

/**
 * @author Axel
 * @brief Fonction permettant d'ignorer une note
 * @param idNote l'id de la note à ignorer
 */
function ignoreNote(idNote)
{
    idNote = parseInt(idNote) // Transforme de string à int

    fetch(`/request/dashboard/ignoreNote/${idNote}`, {
        method: 'GET',
        headers:
            {
                'X-Requested-With': 'XMLHttpRequest'
            }
    })
        .then(response =>
        {
            if (!response.ok)
            {
                throw new Error(response.statusText);
            }

            return response.json();
        })
        .then(data =>
        {
            window.location.reload();
        })
        .catch(error =>
        {
            console.log(error);
        })
}