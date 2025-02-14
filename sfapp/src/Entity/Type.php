<?php

namespace App\Entity;

enum Type : string
{
    case temperature = 'Température';
    case co2 = 'CO2';
    case humidite = 'Humidité';
    case luminosite = 'Luminosité';
}