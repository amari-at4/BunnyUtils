<?php

namespace App\Enum;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

abstract class Base extends Enum
{
    use AutoDiscoveredValuesTrait;

    public function __toString()
    {
        return self::getValue();
    }
}