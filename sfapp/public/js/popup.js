// Définition des messages valides à afficher en vert
const message_valide = [
    "Salle modifiée avec succès",
    "La salle a bien été supprimée",
    "Le batiment a bien été supprimée",
    "Salle ajoutée avec succès",
    "Le système d'acquisition a bien été ajouté",
    "Système d'acquisition modifié avec succès",
    "Le système d'acquisition a bien été supprimé avec succès.",
    "Le système d'acquisition ainsi que son association à une salle ont bien été supprimés avec succès.",
    "Le SA a bien été associé à la salle",
    "Batiment modifiée avec succès",
    "Etage ajouté au batiment",
    "L'étage a bien été supprimée",
    "Le SA a bien été enlevé de la salle",
    "Etage modifiée avec succès",
    "Batiment ajouter avec succès",
    "Vous êtes maintenant connecté.",
    "Vous avez été déconnecté.",
    "Note ajoutée avec succès!",
    "Note modifiée avec succès",
    "Information transmise!",
    "Information ignorée!",
    "Problème lue"
];

// Définition des icônes SVG
const imgValid = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z'/></svg>";
const imgInvalid = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z'/></svg>";

// Ajout de l'évènement au chargement de la page
document.addEventListener('DOMContentLoaded', function () {
    // Récupère l'élément HTML pour le popup
    let message = document.getElementById('popup');
    let messageImg = document.getElementById('popup-img');

    if (message.children[1])
    {
        // Défini les valeurs du popup
        message.style.top = '0vh';
        if(message_valide.includes(message.children[1].innerHTML)) {
            message.style.backgroundColor = '#2BA801';
            messageImg.innerHTML = imgValid;
        } else {
            message.style.backgroundColor = '#c60f0f';
            messageImg.innerHTML = imgInvalid;
        }
        setTimeout(() => {
            message.style.top = '-20vh';
        }, 4000);
    }
    else
    {
        message.style.top = '-20vh';
    }
});