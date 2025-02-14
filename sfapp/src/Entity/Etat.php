<?php

namespace App\Entity;

enum Etat : string
{
    case EnStock = 'En stock';
    case AInstaller = 'À installer';
    case Fonctionnel = 'Fonctionnel';
    case InterventionNecessaire = 'Intervention nécessaire';
}