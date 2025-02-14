// Ajout de l'évènement au chargement de la page
document.addEventListener('DOMContentLoaded', function ()
{
    const searchBtn = document.getElementById('search-btn');
    const searchBar = document.getElementById('search-bar');

    // Ajout de l'évènement de clic sur le bouton 'Rechercher'
    searchBtn.addEventListener('click', () => {
        // Change la route en fonction de la valeur dans la barre de recherche
        if(searchBar.value.length !== 0)
        {
            window.location.href = `/plan/historique/${searchBar.value}`;
        }
        else
        {
            window.location.href = `/plan/historique`;
        }
    })
});
