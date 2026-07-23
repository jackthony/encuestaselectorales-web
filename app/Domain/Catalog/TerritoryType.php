<?php

namespace App\Domain\Catalog;

enum TerritoryType: string
{
    case Region = 'region';
    case Province = 'province';
    case District = 'district';
}
