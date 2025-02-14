<?php

namespace App\Entity;

enum Exposition : string
{
    case Nord = 'Nord';
    case Sud = 'Sud';
    case Ouest = 'Ouest';
    case Est = 'Est';
}