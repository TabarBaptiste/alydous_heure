<?php
namespace App\Enum;

enum Statut: string
{
    case EN_ATTENTE = 'en_attente';
    case CONFIRMEE = 'confirmee';
    case ANNULEE = 'annulee';
}
