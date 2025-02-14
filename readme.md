# SAE34 : Projet SMART-CAMPUS

> Groupe K2-2 :
> - Corentin REMAUD **(PO)**
> - Axel BOURDY **(SM)**
> - Victor GIGNOUX
> - Côme LENAIN
> - Julien RAVILLY

--- 
Contenu : 
- [Prérequis](#prérequis)
- [Démarrage](#démarrage)
  - [1. Cloner le projet Git sur votre machine](#1-cloner-le-projet-git-sur-votre-machine)
  - [2. Démarrer la stack](#2-démarrer-la-stack)
  - [3. Mise en place des jeux de données](#3-mise-en-place-des-jeux-de-données)
- [Accéder à l'application web](#accéder-à-lapplication-web)
- [Arrêt de la stack](#arrêt-de-la-stack)

--- 

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments requis sur votre machine :
- Docker 24
- Docker Engine sous Linux *(ne pas installer Docker Desktop sous Linux)*
- Docker Desktop sous Mac ou Windows
- Git
- Git Bash sous Windows

> Pour les utilisateurs Windows, il est conseillé d'utiliser WSL pour de meilleures performances.

## Démarrage

### 1. Cloner le projet Git sur votre machine

Choisissez le répertoire où seront stockés les fichiers de l'application web, puis positionnez-vous dans ce répertoire. Ensuite, exécutez la commande suivante dans un terminal (sur Windows, utilisez Git Bash) :
```bash
git clone https://forge.iut-larochelle.fr/2024-2025-but-info2-a-sae34/k2/k22/web_k22.git
```

### 2. Démarrer la stack 

Positionnez-vous à l'intérieur du répertoire qui a été précédemment créé.

⚠️ **Si vous êtes sous Linux**  
> Avant de démarrer la stack, il faut renseigner les variables qui se trouvent dans le fichier `.env` à la racine du dépôt     
> Vous pouvez obtenir l'id de votre user (et de son groupe) en lançant la commande `id -u ${USER}` dans un terminal

Exécuter la commande suivante :
```bash
docker compose up -d --build
```

Vous pouvez vérifier ensuite l'état de la stack avec la commande `docker compose ps`.

### 3. Mise en place des jeux de données

Une fois votre stack démarré, ouvrez un terminal dans le conteneur de l'application :
```bash
docker compose exec -it sfapp bash
```

Puis, positionnez-vous dans le dossier du projet :
```bash
cd sfapp
```

Une fois dans le dossier `/app/sfapp`, exécuter les migrations pour construire la base de données :
```bash
php bin/console doc:mig:mig
```

Ensuite, exécutez les fixtures pour peupler cette base de données :
```bash
php bin/console doc:fix:load
```

Confirmez que vous souhaitez écraser les données de la base, puis patientez le temps que les fixtures s'exécutent.

Enfin, pour lancer le rafraichissement des données avec celles de l'API, exécuter la commande :
```bash
php bin/console app:run_scheduler
```

## Accéder à l'application web

Une fois l'application démarré, vous pouvez y accéder depuis votre machine à l'adresse suivante :
```
http://localhost:8000/
```

## Arrêt de la stack

Si vous êtes encore dans un terminal de la stack, vous pouvez le quitter à l'aide de la commande `exit`.

Pour arrêter le conteneur, il vous suffit d'exécuter la commande suivante :
```bash
docker compose down
```

Pour relancer le conteneur, il vous suffit de réitérer les étapes depuis l'étape de [démarrage de la stack](#2-démarrer-la-stack).
