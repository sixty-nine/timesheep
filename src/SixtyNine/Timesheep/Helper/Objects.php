<?php

namespace SixtyNine\Timesheep\Helper;

class Objects
{
    public static function implements(object $class, string $interface): bool
    {
        $implements = class_implements($class);
        if (!$implements) {
            return false;
        }
        return array_key_exists($interface, $implements);
    }
}
