<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MedicineForm: string implements HasLabel
{
    case Tablet = 'tablet';
    case Capsule = 'capsule';
    case Syrup = 'syrup';
    case Injection = 'injection';
    case Ointment = 'ointment';
    case Drops = 'drops';
    case Lotion = 'lotion';
    case Powder = 'powder';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
