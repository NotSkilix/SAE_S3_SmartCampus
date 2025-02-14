// Liste des types de capteurs souhaités pour calculer le score global de confort environnemental
const sensorTypes = ['Température', 'CO2', 'Humidité']

// Définition des valeurs minimales et maximales des différents capteurs
const minTemp = 17;
const maxTemp = 21;
const minCO2 = 1000;
const maxCO2 = 1500;
const minHum = 70;
const maxHum = 100;

/**
 * @author Julien
 * @brief Récupère par une requête AJAX les valeurs des capteurs, nécessaires pour le calcul du score, d'une salle.
 * @param salleId Identifiant de la salle souhaitée
 * @returns {Promise<Map<any, any>>} Dictionnaire des valeurs des capteurs
 */
async function getSensorsValue(salleId) {
    let values = new Map();

    try {
        // Requête AJAX récupérant les valeurs des capteurs
        const response = await fetch(`/request/dashboard/getSensorsFromSalle/${salleId}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        // Vérification du bon déroulement de la requête
        if (!response.ok) throw new Error(response.statusText);

        const data = await response.json();
        data.forEach(elem => {
            // Rajoute les valeurs des capteurs souhaitées dans le dictionnaire
            if (sensorTypes.includes(elem.type)) {
                values.set(elem.type, elem.valeur);
            }
        });

        // Retourne la Map remplie
        return values;
    } catch (error) {
        console.error(error);
        // Retourne quand même la Map en cas d'erreur
        return values;
    }
}

/**
 * @author Julien
 * @brief Calcul le score de confort environnemental d'une salle.
 * @param salleId Identifiant de la salle souhaitée
 * @returns {Promise<number|null>} Score de la salle
 */
async function getSalleScore(salleId) {
    // Attend que les valeurs soient récupérées
    let values = await getSensorsValue(salleId);
    for (const type of sensorTypes) {
        if ((!values.has(type)) || (values.get(type) == null)){
            return null;
        }
    }

    // Calcul des scores par capteurs
    let d_temp = ((values.get('Température') - minTemp) * 100 / (maxTemp - minTemp));
    if (values.get('Température') > 21) d_temp = 100 - d_temp;
    let d_co2 = 100 - ((values.get('CO2') - minCO2) * 100 / (maxCO2 - minCO2));
    let d_hum = 100 - ((values.get('Humidité') - minHum) * 100 / (maxHum - minHum));

    // Défini les valeurs entre 0 et 100
    if (d_temp < 0) { d_temp = 0; } else if (d_temp > 100) { d_temp = 100; }
    if (d_co2 < 0) { d_co2 = 0; } else if (d_co2 > 100) { d_co2 = 100; }
    if (d_hum < 0) { d_hum = 0; } else if (d_hum > 100) { d_hum = 100; }

    // Retourne la moyenne des 3 valeurs
    return (d_temp + d_co2 + d_hum) / 3;
}

/**
 * @author Julien
 * @brief Récupère par une requête AJAX les identifiants des salles d'un bâtiment
 * @returns {Promise<*[]>} Liste des identifiants des salles
 */
async function getBatimentSalles()
{
    let salles = [];

    try {
        // Requête AJAX récupérant les identifiants des salles du bâtiment sélectionné
        const response = await fetch("/request/dashboard/getSallesFromActualBatiment", {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        // Vérification du bon déroulement de la requête
        if (!response.ok) throw new Error(response.statusText);

        const data = await response.json();
        data.forEach(elem => {
            // Rajoute l'identifiant de la salle à la liste
            salles.push(elem.id)
        });

        // Retourne la liste remplie
        return salles;
    } catch (error) {
        console.error(error);
        // Retourne quand même la liste en cas d'erreur
        return salles;
    }
}

/**
 * @author Julien
 * @brief Calcul le score global de confort environnemental du bâtiment sélectionné.
 * @returns {Promise<number|null>} Score calculé
 */
async function getBatimentScore()
{
    let sallesScore = [];

    // Attend que les id des salles soient récupérés
    let sallesId = await getBatimentSalles();
    for (const id of sallesId) {
        // Attend que le score de la salle soit récupéré
        const score = await getSalleScore(id);
        // Si existant, ajoute le score de la salle à la liste
        if (score) sallesScore.push(score);
    }

    if (sallesScore.length <= 0) return null;

    // Calcul de la somme des scores des salles
    let scoreSum = 0;
    sallesScore.forEach(score => {
        scoreSum += score;
    })

    // Retourne le score global en divisant la somme par la longueur de la liste
    return scoreSum / sallesScore.length;
}
