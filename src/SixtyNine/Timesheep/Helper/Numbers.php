<?php

namespace SixtyNine\Timesheep\Helper;

class Numbers
{
    /**
     * @var string[]
     */
    private static $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    public static function humanFileSize(int $size, int $precision = 2): string
    {
        $i = (int)log($size, 1024);

        $rounded = round($size / (1024 ** $i), $precision);
        if ($precision !== 0 && strpos((string)$rounded, '.') === false) {
            $rounded .= '.' . str_repeat('0', $precision);
        }

        return $rounded . self::$units[$i];
    }
}
