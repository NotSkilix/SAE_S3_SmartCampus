<?php

namespace App\Entity;

enum TypeNote : string
{
    case Information = 'Information';
    case Probleme = 'Probleme';
    case ProblemeLu = 'ProblemeLu';
    case ProblemeBatimentNonEnvoye = 'ProblemeBatimentNonEnvoye';
    case ProblemeBatiment = 'ProblemeBatiment';
    case ProblemeBatimentLu = 'ProblemeBatimentLu';
    case ProblemeBatimentIgnore = 'ProblemeBatimentIgnore';
}
