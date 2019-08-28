<?php

namespace SixtyNine\Timesheep\Helper;

class Objects
{
    public static function implements($class, $interface): bool
    {
        return array_key_exists($interface, class_implements($class));
    }
}
