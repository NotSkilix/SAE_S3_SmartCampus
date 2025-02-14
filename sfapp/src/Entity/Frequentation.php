<?php

namespace App\Entity;

enum Frequentation : string
{
    case TresFrequente = 'Très fréquenté';
    case Frequente = 'Fréquenté';
    case PeuFrequente = 'Peu fréquenté';
}