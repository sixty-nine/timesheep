<?php

namespace SixtyNine\Timesheep\Helper;

class Numbers
{
    // From https://gist.github.com/liunian/9338301#gistcomment-1570375
    public static function humanFileSize($size, $precision = 2): string
    {
        for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {
            // do nothing
        }
        return round($size, $precision) . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
    }
}
