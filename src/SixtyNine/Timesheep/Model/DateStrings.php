<?php

namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;

class DateStrings
{
    /**
     * Check if the given string is a valid date according to the format.
     * @param string $date
     * @param string $format
     * @return bool
     */
    public function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateObj = DateTimeImmutable::createFromFormat($format, $date);
        return $dateObj && $dateObj->format($format) === $date;
    }

    /**
     * Check if the given string is a valid time according to the format.
     * Nice wrapper around isValidDate with time format.
     * @param string $time
     * @param string $format
     * @return bool
     */
    public function isValidTime(string $time, $format = 'H:i'): bool
    {
        return $this->isValidDate($time, $format);
    }

    /**
     * Convert a decimal time to a HH:MM formatted string.
     * @param float $decimal
     * @return string
     */
    public function decimalToTime(float $decimal): string
    {
        $hours = floor($decimal % 60);
        $minutes = round(($decimal - (int)$decimal) * 60);
        return sprintf(
            '%000s:%02s',
            floor($decimal ),
            round(($decimal - (int)$decimal) * 60)
        );
    }
}
