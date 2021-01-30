<?php

namespace SixtyNine\Timesheep\Helper;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class DateTimeHelper
{
    public function mutableFromImmutable(DateTimeImmutable $in)
    {
        return DateTime::createFromFormat(
            DateTimeInterface::ATOM,
            $in->format(DateTimeInterface::ATOM)
        );
    }

    /**
     * From https://stackoverflow.com/a/33195692/643106
     * @param DateTimeImmutable $datetime
     * @param int $precision
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function roundTime(DateTimeImmutable $datetime, $precision = 30): DateTimeImmutable
    {
        if ($precision === 0) {
            return $datetime;
        }

        $dt = $this->mutableFromImmutable($datetime);

        // 1) Set number of seconds to 0 (by rounding up to the nearest minute if necessary)
        $second = (int)$dt->format('s');
        if ($second > 30) {
            // Jumps to the next minute
            $dt->add(new DateInterval('PT' . (60 - $second) . 'S'));
        } elseif ($second > 0) {
            // Back to 0 seconds on current minute
            $dt->sub(new DateInterval('PT' . $second . 'S'));
        }
        // 2) Get minute
        $minute = (int)$dt->format('i');
        // 3) Convert modulo $precision
        $minute %= $precision;
        if ($minute > 0) {
            // 4) Count minutes to next $precision-multiple minutes
            $diff = $precision - $minute;
            // 5) Add the difference to the original date time
            $dt->add(new DateInterval('PT' . $diff . 'M'));
        }

        return DateTimeImmutable::createFromMutable($dt);
    }

    /**
     * @param float $decimal
     * @return string
     */
    public function decimalToTime(float $decimal): string
    {
        return sprintf(
            '%s:%02s',
            floor($decimal % 60),
            round(($decimal - (int)$decimal) * 60)
        );
    }

    /**
     * @param array $dates
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getFirstNotNullOrToday(array $dates): DateTimeImmutable
    {
        foreach ($dates as $date) {
            if (null !== $date) {
                return $date;
            }
        }
        return new DateTimeImmutable();
    }
}
