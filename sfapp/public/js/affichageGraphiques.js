// Définir la map avec noms de l'ESP en clé et dbnames en valeur
const nomsDbMap = new Map([
    ['D205','sae34bdk1eq1'],     //D205
    ['D206', 'sae34bdk1eq2'],    //D206
    ['D207', 'sae34bdk1eq3'],    //D207
    ['D204', 'sae34bdk2eq1'],    //D204
    ['D203', 'sae34bdk2eq2'],    //D203
    ['D303', 'sae34bdk2eq3'],    //D303
    ['D304', 'sae34bdl1eq1'],    //D304
    ['C101', 'sae34bdl1eq2'],    //C101
    ['D109', 'sae34bdl1eq3'],    //D109
    ['Secrétariat', 'sae34bdl2eq1'],    //Secrétariat
    ['D001', 'sae34bdl2eq2'],    //D001
    ['D002', 'sae34bdl2eq3'],    //D002
    ['D004', 'sae34bdm1eq1'],    //D004
    ['C004', 'sae34bdm1eq2'],    //C004
    ['C007', 'sae34bdm1eq3'],    //C007
]);

//variable du graphique
let chart; // graphique
let currentSensor = 'temp';        // le capteur choisi actuellement
let currentLocalisation;  // la salle ou batiment séléctionné actuellement
let isWindowSlided = false;
let jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
let mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

// map pour la configuration des graphiques en fonction du capteur choisi
// [titre Y, seuil min, seuil max, pas]
const labelsBySensors = [
  ["temp", ["Température (°C)", 17, 21, 1]],
  ["co2", ["Taux de CO2 (ppm)", -1, 1500, 200]],
  ["hum", ["Taux d'humidité (%)", -1, 70, 10]]
];
const labelsBySensorsMap = new Map(labelsBySensors);

const startDate = document.getElementById('start-date');              // input date de début
const endDate = document.getElementById('end-date');                  // input date de fin
const chartContainer = document.getElementById('chart-content');      // conteneur du graphique
const typeDateOption = document.getElementById('chart-options');      // liste des types de dates
const dateFilter = document.getElementById('chart-options');          // filtre du type de date
const movingPart = document.getElementById('moving-part');            // page qui slide
const buttonIndex = document.getElementById('nav-button-index');      // onglet vers l'indice de confort
const buttonSensors = document.getElementById('nav-button-sensors');  // onglet vers le capteur
const optionMonth = document.getElementById('option-month');          // option mois à désactiver quand il n'y a pas plusieurs mois dans le graphique
const optionDay = document.getElementById('option-day');              // option jour à désactiver quand il n'y a pas plusieurs jours dans le graphique
const chartLoading = document.getElementById('chart-loading');        // indication de chargement du graphique
const chartCenter = document.getElementById('chart-center-checkbox'); // checkbox pour centrer le graphique
const localisationElement = document.getElementById('localisation');  // localisation choisie
const buttonSensorTemp = document.getElementById('sensors-buttons-left-avg-temp'); // bouton du capteur de temp
const buttonSensorCo2 = document.getElementById('sensors-buttons-left-avg-co2'); // bouton du capteur de co2
const buttonSensorHum = document.getElementById('sensors-buttons-left-avg-hum'); // bouton du capteur d'humidité
const sensorButtons = document.querySelectorAll('.sensor-button'); // boutons des capteurs
const globalChartContainer = document.getElementById('chart-container'); // container du graphique
const selectSalle = document.getElementById('select-salle'); // liste des salles
const currentScore = document.getElementById('current-score'); // affichage du score actuel

document.addEventListener("DOMContentLoaded", () => {

  // permet de formater la date en 'YYYY-MM-DD'
  const formatDate = (date) => date.toISOString().split('T')[0];
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(today.getDate() + 1);

  if(sessionStorage.getItem('startDate') && sessionStorage.getItem('endDate'))
  {
    // s'il y à déjà des dates enregistrées
    startDate.lastChild.value = sessionStorage.getItem('startDate');
    endDate.lastChild.value = sessionStorage.getItem('endDate');
  }
  else
  {
    // Définir la date d'aujourd'hui
    startDate.lastChild.value = formatDate(today);    
    endDate.lastChild.value = formatDate(tomorrow);
  }
  //définition des dates min et max par défaut
  startDate.lastChild.max = endDate.lastChild.value;
  endDate.lastChild.min = startDate.lastChild.value;
  endDate.lastChild.max = formatDate(tomorrow);
  selectSalle.value = localisationElement.getAttribute('data-localisation');

  // premier affichage du graphique
  displayChart();

  //reload l'affichage quand on change de filtre de date
  typeDateOption.addEventListener('change', () => {
    displayChart();
  });

  // reload le graphique quand on change de date
  startDate.addEventListener('change', () => {
    displayChart();
    sessionStorage.setItem('startDate', startDate.lastChild.value);
    sessionStorage.setItem('endDate', endDate.lastChild.value);
    endDate.lastChild.min = startDate.lastChild.value; // modification de la date min
  });

  endDate.addEventListener('change', () => {
    displayChart();
    sessionStorage.setItem('startDate', startDate.lastChild.value);
    sessionStorage.setItem('endDate', endDate.lastChild.value);
    startDate.lastChild.max = endDate.lastChild.value; // modification de la date max
  });

  chartCenter.addEventListener('change', () => {
    displayChart();
  });

  selectSalle.addEventListener('change', () => {
    window.location.href = `/diagnostic/${selectSalle.value}`;
  })
});

async function score()
{
  // début de l'affichage du graphique
  chartLoading.style.display = "flex";

  globalChartContainer.style.left = '30vw';
  sensorButtons.forEach(button => {
    button.style.transform = 'scale(0)';
  });

  // --- affichage score global ---
  let dataTemp = await fetchCaptures(currentLocalisation, 'temp');
  let dataCo2 = await fetchCaptures(currentLocalisation, 'co2');
  let dataHum = await fetchCaptures(currentLocalisation, 'hum');

  dataTemp = compareDate(dataTemp);
  dataCo2 = compareDate(dataCo2);
  dataHum = compareDate(dataHum);

  values = [];
  dates = dataTemp[0];

  for(let i = 0; i < dataTemp[0].length; i++)
  {
    let scoreTemp, scoreCo2, scoreHum;
    let d_temp = (dataTemp[1][i] - 17) * 100 / (21 - 17);
    if (dataTemp[1][i] > 21) d_temp = 100 - d_temp;
    let d_co2 = 100 - (dataCo2[1][i] - 1000) * 100 / (1500 - 1000);
    let d_hum = 100 - (dataHum[1][i] - 70) * 100 / (100 - 70);

    // Défini les valeurs entre 0 et 100
    if (d_temp < 0) { d_temp = 0; } else if (d_temp > 100) { d_temp = 100; }
    if (d_co2 < 0) { d_co2 = 0; } else if (d_co2 > 100) { d_co2 = 100; }
    if (d_hum < 0) { d_hum = 0; } else if (d_hum > 100) { d_hum = 100; }

    let score = (d_temp + d_co2 + d_hum)/3;
    values.push(Math.round(score));

    if(i+1 == dataTemp[0].length)
    {
      if(isNaN(score))
        currentScore.innerHTML = "?%";
      else
        currentScore.innerHTML = `${Math.round(score)}%`;
    }
  }

  let moyenne = avgArray(values);

  // récupération de l'élément html graphique
  const ctx = document.getElementById('chart');

  if (chart) {
    chart.destroy(); // Détruire l'instance graphique précédente
  }

  //création du graphiquebackgroundColor
  let datasets = [
    {
      label: 'score global de confort', // titre du graphique
      data: values, //valeurs pour chaque label
      borderWidth: 1.3,
      borderColor: '#2BA801',
      backgroundColor: '#2BA801',
      pointStyle: false,
      tension: 0.3,
    },
    {
      label: 'Moyenne', // titre du graphique
      data: Array(sensorsData[0].length).fill(moyenne), //valeurs pour chaque label
      borderWidth: 1.5,
      backgroundColor: '#EFEFEF',
      borderColor: '#000000',
      pointStyle: false,
      borderDash: [5, 5],
    }
  ];

  chart = new Chart(ctx, {
    type: 'line', // type du graphique [bar, line, doughnut, ...]
    data: {
      labels: dates, // ligne du bas
      datasets: datasets,
    },
    options: {
      responsive: true,
      scales: {
        y: {
          title: {
            display: true,
            text: 'score',
          },
          min: 0,
          max: 100,
          ticks: {
            stepSize: 10, // définir les intervalles entre les ticks
          },
        },
        x: {
          title: {
            display: true,
            text: 'Dates des captures',
          },
        },
      },
    },
  });

  // fin de l'affichage du graphique
  chartLoading.style.display = "none";
}

/**
 * @autor Victor
 * @brief Affiche un nouveau graphique en fonction de la localisation choisie
 */
function displayChart(){

  // affichage du graphique
  currentLocalisation = localisationElement.getAttribute('data-localisation');
  if(isWindowSlided)
    score();
  else
  {
    displayGraphique(currentLocalisation, currentSensor);
    // affichage des moyennes
    displayAVG(currentLocalisation);
  }
  typeDateOption.disabled = false;
}

/**
 * @autor Victor
 * @brief Change le capteur choisi et affiche le nouveau graphique
 *        appelée quand on clique sur les boutons des capteurs
 * @param string capteur : type du capteur séléctionné
 */
function setGraphiqueCapteur(capteur)
{
  currentSensor = capteur;
  displayGraphique(currentLocalisation, currentSensor);
}

/**
 * @autor Victor
 * @brief Fonction asynchrone pour effectuer la requête de l'API en parrallele des autres scripts
 * @param string localisation = nom de la salle ou batiment concerné par la recherche de valeur
 * @param string capteur = type du capteur dont on veut la valeur
 * @return array : un tableau au format json des données recherchées
 */
async function fetchCaptures(localisation, capteur) {

    // Créer l'URL pour avoir la derniere valeur
    const url = `https://sae34.k8s.iut-larochelle.fr/api/captures/interval?date1=${startDate.lastChild.value}&date2=${endDate.lastChild.value}&nom=${capteur}&localisation=${localisation}`;

    // Définir les en-têtes de la requête
    const headers = {
        'accept': 'application/json',
        'dbname': nomsDbMap.get(localisation),
        'username': 'k2eq2',
        'userpass': 'zobdaN-tigqy2-nucsyb'
    };

    if(startDate.lastChild.value != "" && endDate.lastChild.value != "")
    {
      try {
          // Faire la requête GET avec fetch
          const response = await fetch(url, {
              method: 'GET',
              headers: headers
          });

          // Vérifier si la réponse est OK (statut 200)
          if (!response.ok) {
            return -1;
            //throw new Error('Network response was not ok ' + response.statusText);
          }

          // Transformer la réponse en JSON
          const data = await response.json();
          //vérifie qu'il y a une valeur
          if(data.length!==0)
            return data;
          // retourne -1 si aucune valeur pour tous les autres
          else
            return -1;

      } catch (error) {
          console.error('Erreur de la requête:', error); // Gérer les erreurs
      }
    }
    else
    {
      return -1;
    }
}

/**
 * @autor Victor
 * @brief Configure et affiche un graphique en fonction de la localisation et du capteur choisi
 * @param string localisation : salle ou batiment choisi
 * @param string capteur : capteur choisi
 */
async function displayGraphique(localisation, capteur)
{
  // début de l'affichage du graphique
  chartLoading.style.display = "flex";

  globalChartContainer.style.left = '40vw';
  sensorButtons.forEach(button => {
    button.style.transform = 'scale(1)';
  });

  // définiton de la couleur du graphique en fonction du capteur
  color = setBackgroundColor(capteur);

  // récupération des données de l'API
  sensorsData = await fetchCaptures(localisation, capteur);

  if(sensorsData != -1) //
  {
    let graphiqueDescription = sensorsData[0].description; // titre du graphique

    //compare les date de début et fin pour activer ou désactiver l'option mois
    sensorsData = compareDate(sensorsData);

    // récupération de la config du graphique en fonction du capteur
    let [labelY, minY, maxY, step] = labelsBySensorsMap.get(capteur);
    let avgValue = avgArray(sensorsData[1]); // moyenne des valeurs capturées

    // valeurs min et max des données capturées
    let minData = Math.min(...sensorsData[1]);
    let maxData = Math.max(...sensorsData[1]);

    if(chartCenter.checked)
    {
      // affichage du graphique en fonction des valeurs capturées au lieu des seuil définis
      let difference = (maxData - minData)/10;
      step = Math.round(difference);
      minData -= difference;
      maxData += difference;
    }
    else
    {
      console.log(minData, maxData, minY, maxY);

      // ajustement en cas de dépacement des min et max par défaut
      if(maxData > maxY)
        maxData = Math.round(maxData + step);
      else
        maxData = Math.round(maxY + step);

      if(minData < 0)
        minData = Math.round(minData - step);
      else
        minData = 0;
    }
    
    // récupération de l'élément html graphique
    const ctx = document.getElementById('chart');

    if (chart) {
      chart.destroy(); // Détruire l'instance graphique précédente
    }

    //création du graphique
    let datasets = [
      {
        label: graphiqueDescription, // titre du graphique
        data: sensorsData[1], //valeurs pour chaque label
        borderWidth: 1.3,
        borderColor: color,
        backgroundColor: color,
        pointStyle: false,
        tension: 0.3,
      },
      {
        label: 'Seuil maximal de confort', // titre du graphique
        data: Array(sensorsData[0].length).fill(maxY), //valeurs pour chaque label
        borderWidth: 1.5,
        backgroundColor: '#EFEFEF',
        borderColor: '#AAAAAA',
        pointStyle: false,
        borderDash: [5, 5],
      },
      {
        label: 'Moyenne', // titre du graphique
        data: Array(sensorsData[0].length).fill(avgValue), //valeurs pour chaque label
        borderWidth: 1.5,
        backgroundColor: '#EFEFEF',
        borderColor: color,
        pointStyle: false,
        borderDash: [5, 5],
      }
    ];

    // Condition pour afficher la moyenne
    if (minY != -1) {
      datasets.push({
        label: 'Seuil minimal de confort', // titre du graphique
        data: Array(sensorsData[0].length).fill(minY), //valeurs pour chaque label
        borderWidth: 1.5,
        backgroundColor: '#EFEFEF',
        borderColor: '#AAAAAA',
        pointStyle: false,
        borderDash: [5, 5],
      });
    }

    chart = new Chart(ctx, {
      type: 'line', // type du graphique [bar, line, doughnut, ...]
      data: {
        labels: sensorsData[0], // ligne du bas
        datasets: datasets,
      },
      options: {
        responsive: true,
        scales: {
          y: {
            title: {
              display: true,
              text: labelY,
            },
            min: minData,
            max: maxData,
            ticks: {
              stepSize: step, // définir les intervalles entre les ticks
            },
          },
          x: {
            title: {
              display: true,
              text: 'Dates des captures',
            },
          },
        },
      },
    });
  }
  else
  {
    // s'il n'y a pas de données récupérées
    chartContainer.innerHTML = `
      <div>Aucune donnée trouvée</div>
    `;
    typeDateOption.disabled = true;
  }

  // fin de l'affichage du graphique et du chargement
  chartLoading.style.display = "none";
}

/**
 * @autor Victor
 * @brief Affiche les moyennes des capteurs
 * @param string localisation : salle ou batiment actuel
 */
async function displayAVG(localisation)
{
  buttonSensorTemp.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>`;
  buttonSensorCo2.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>`;
  buttonSensorHum.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>`;

  let dataBySensor = [
    [await fetchCaptures(localisation, "temp"), buttonSensorTemp],
    [await fetchCaptures(localisation, "co2"), buttonSensorCo2],
    [await fetchCaptures(localisation, "hum"), buttonSensorHum]
  ];

  for([datas, element] of dataBySensor)
  {
    if(datas != -1)
      element.innerHTML = avg(datas).toString();
    else
      element.innerHTML = "?";
  };
}

/**
 * @autor Victor
 * @brief Segmente une date pour n'en garder qu'une partie 
 *        en fonction du type de date choisi (mois, jour, heure et minutes)
 * @param string dateString : date au format 'yyyy-mm-dd hh-mm-ss'
 * @return string : date au format choisi
 */
function convertDate(dateString) {
  // Sépare la partie date et la partie heure
  const [days, hours] = dateString.split(' ');
  // Sépare les heures, minutes et secondes
  const [year, month, day] = days.split('-');
  // Sépare les heures, minutes et secondes
  const [hour, minute, second] = hours.split(':');
  let date = new Date(days+"T"+hours);
  // récupération du jour et du mois
  let jourSemaine = jours[date.getDay()];
  let nomMois = mois[date.getMonth()];

  if(typeof nomMois === 'undefined')
  {
    return -1;
  }
  else if(dateFilter.value === "hours&minutes")
  {
    // Retourne uniquement l'heure et les minutes
    return `${jourSemaine} ${day} ${hour}:${minute}`;
  }
  else if(dateFilter.value === "day")
  {
    // Retourne uniquement l'heure et les minutes
    return `${jourSemaine} ${day} ${nomMois}`;
  }
  else if(dateFilter.value === "month")
  {
    
    // Retourne uniquement l'heure et les minutes
    return `${nomMois} ${year}`;
  }
}

/**
 * @autor Victor
 * @brief Modifie la couleur du graphique
 * @param string capteur : type du capteur
 */
function setBackgroundColor(capteur)
{
  // récupération des boutons des capteurs
  const temp = document.getElementById('sensor-button-temp');
  const co2 = document.getElementById('sensor-button-co2');
  const hum = document.getElementById('sensor-button-hum');

  //reset
  temp.classList.remove('current-sensor-button');
  co2.classList.remove('current-sensor-button');
  hum.classList.remove('current-sensor-button');

  let color;
  if(capteur === "temp")
  {
    temp.classList.add('current-sensor-button');
    color = '#FD1900';
  }
  else if(capteur === "co2")
  {
    co2.classList.add('current-sensor-button');
    color = '#7C3E92';
  }
  else
  {
    hum.classList.add('current-sensor-button');
    color = '#4493D3';
  }

  return color;
}

/**
 * @autor Victor
 * @brief Calcule la moyenne des valeurs données
 * @param data : tableau d'objets qui contiennentla date et la valeur d'une requête à l'API
 */
function avg(data)
{
  let sum = 0;
  for(let i = 0; i < data.length; i++)
  {
    
    let currentValue = parseFloat(data[i].valeur);
    if(typeof currentValue === "number" && !isNaN(currentValue))
      sum += currentValue;
  }
  return (sum / data.length).toFixed(2);
}

/**
 * @autor Victor
 * @brief Calcule la moyenne des valeurs données
 * @param array data : tableau de valeurs de type int
 */
function avgArray(data)
{
  let sum = 0;
  for(let i = 0; i < data.length; i++)
  {
    let currentValue = parseFloat(data[i]);
    if(typeof currentValue === "number" && !isNaN(currentValue))
      sum += currentValue;
  }
  return (sum / data.length).toFixed(2);
}

/**
 * @autor Victor
 * @brief Calcule la moyenne  des valeurs pour chaque jour ou chaque mois
 * @param array : tableau d'objets qui contiennentla date et la valeur d'une requête à l'API
 */
function convertByDateType(array)
{
  let values = [[]], dates = [];
  let currentDate, i = 0;
  if(dateFilter.value === "day" || dateFilter.value === "month")
  {
    // initialisation avec les premières valeur et date
    currentDate = convertDate(array[0].dateCapture);
    dates.push(currentDate);
    array.forEach(data => {
      values[i].push(data.valeur); // on ajoute la valeur avec les autres valeurs de la même date
      nextDate = convertDate(data.dateCapture); // on regarde la prochaine date
      if(nextDate != currentDate && nextDate != -1) // si on change de date
      {
        currentDate = nextDate; // on change la date actuelle
        dates.push(currentDate);
        //values.push([]); // ajout d'un nouveau tableau pour les valeurs de cette nouvelle date
        i++; // incrémentation de l'indice des tableaux de valeurs
        values[i] = [];
      }
    });
    // on fait la moyenne de chaque tableau de valeurs
    for(let i = 0; i < values.length; i++)
    {
      values[i] = avgArray(values[i]);
    }
  }
  else
  {
    values = [];
    dates = [];
    array.forEach(data => {
      const date = convertDate(data.dateCapture);
      if(date != -1)
      {
        values.push(data.valeur);
        dates.push(date);
      }
    });
  }
  return [dates, values];
}

/**
 * @autor Victor
 * @brief Défile la page vers la droite ou vers la gauche
 * @param int direction : prend 1 (droite) ou -1 (gauche)
 */
function slideWindow(direction)
{
  // vers l'indice de confort
  if(direction === -1)
  {
    buttonIndex.classList.add('selected');
    buttonSensors.classList.remove('selected');
    isWindowSlided = true;
    score();
  }
  // vers la pages des capteurs
  else
  {
    buttonIndex.classList.remove('selected');
    buttonSensors.classList.add('selected');
    isWindowSlided = false;
    displayChart();
  }
}

/**
 * @autor Victor
 * @brief Teste l'affichage des valeurs pour chaque type de date pour savoir lesquels désactiver
 * @param data : 
 * @return les data en fonction du type de date correct
 */
function compareDate(data)
{
  // on stock la valeur du filtre actuelle
  const currentFilter = dateFilter.value;

  // on vérifie le nombre de valeur par mois
  dateFilter.value = "month";
  sensorsData = convertByDateType(data);
  //si il y a qu'un seul mois
  if(sensorsData[0].length <= 1)
  {
    // on passe en jour et on désactive le mois
    optionMonth.disabled = true;
    // même test avec les jours
    dateFilter.value = "day";
    sensorsData = convertByDateType(data);
    if(sensorsData[0].length <= 1)
    {
      optionDay.disabled = true;
      dateFilter.value = "hours&minutes";
      sensorsData = convertByDateType(data);
    }
    else
    {
      optionDay.disabled = false;
    }
  }
  else
  {
    optionMonth.disabled = false;
    optionDay.disabled = false;
  }

  // choisi le bon type de date en fonction des options actives
  if(optionDay.disabled && currentFilter != "all")
    dateFilter.value = "hours&minutes";
  else if(optionMonth.disabled && currentFilter != "hours&minutes")
    dateFilter.value = "day";
  else
    dateFilter.value = currentFilter;
  
  sensorsData = convertByDateType(data);
  return sensorsData;
}