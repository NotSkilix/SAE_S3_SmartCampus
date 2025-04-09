# SAE34 : Projet SMART-CAMPUS

> Groupe K2-2 :
> - Corentin **(PO)**
> - Axel **(SM)**
> - Victor
> - Côme
> - Julien

--- 
Contenu : 
- [Prérequis](#prérequis)
- [Démarrage](#démarrage)
  - [1. Cloner le projet Git sur votre machine](#1-cloner-le-projet-git-sur-votre-machine)
  - [2. Démarrer la stack](#2-démarrer-la-stack)
  - [3. Mise en place des jeux de données](#3-mise-en-place-des-jeux-de-données)
- [Accéder à l'application web](#accéder-à-lapplication-web)
- [Arrêt de la stack](#arrêt-de-la-stack)
- [Identifiants](#identifiants)
  - [Technicien](#technicien)
  - [Chargé de mission](#chargé-de-mission)

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

N'oubliez pas d'installer les dépendances du projet
```bash
composer install
```

Une fois les dépendances installées, exécuter les migrations pour construire la base de données :
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
php bin/console app:run-scheduler
```

## Accéder à l'application web

Une fois l'application démarré, vous pouvez y accéder depuis votre machine à l'adresse suivante :
```
http://localhost:8000/
```

## Arrêt de la stack

Si vous êtes encore dans un terminal de la stack, vous pouvez le quitter à l'aide de la commande `exit`.

Pour simplement arrêter la stack docker:
```bash
docker compose stop
```

Pour arrêter et détruire la stack, il vous suffit d'exécuter la commande suivante :
```bash
docker compose down
```

Pour relancer le conteneur (après un `docker compose stop`), il vous suffit de faire la commande suivante:
```bash
docker compose up -d
```

## Identifiants
Comme vous l'avez sûrement remarqué, vous pouvez vous connecter en bas à droite de l'écran: \
![image](https://github.com/user-attachments/assets/3e2dcfbb-9237-4e63-a4ff-ab348faa4371) \
Cela permet d'avoir l'affichage du **technicien** ainsi que du **chargé de mission**.

### Technicien
- **Identifiant:** `technicien`
- **Mot de passe:** `smart-campus`

### Chargé de mission
- **Identifiant:** `chargemission`
- **Mot de passe:** `smart-campus`
