<?php

namespace App\Entity;

enum TypeConseil : string
{
    case temp = "Température";
    case hum = "Humidité";
    case co2 = "CO2";
    case lum = "Luminosité";
    case gpu = "GPU";
}
