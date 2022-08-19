<?php

namespace SixtyNine\Timesheep;

use SixtyNine\Timesheep\Helper\Arrays;

class Config
{
    /** @var array<array> */
    protected $config = [];

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $dateFormat = getenv('DATE_FORMAT');
        $timeFormat = getenv('TIME_FORMAT');
        $this->config = [
            'db' => [
                'url' => getenv('TIMESHEEP_DB_URL'),
            ],
            'console' => [
                'box-style' => getenv('BOX_STYLE') ?: 'default',
            ],
            'time' => [
                'rounding' => getenv('TIME_ROUNDING') ?: '5',
                'hours-per-day' => getenv('HOURS_DUE_PER_DAY'),
                'occupation-rate' => getenv('OCCUPATION_RATE'),
            ],
            'format' => [
                'date' => $dateFormat ?: 'd-m-Y',
                'time' => $timeFormat ?: 'H:i',
                'datetime' => $dateFormat && $timeFormat ? $dateFormat.' '.$timeFormat: 'd-m-Y H:i',
            ],
        ];
    }

    public function get(string $key): string
    {
        $indexes = explode('.', $key);
        $curItem = $this->config;
        foreach ($indexes as $idx) {
            if (!isset($curItem[$idx])) {
                return '';
            }
            $curItem = $curItem[$idx];
        }
        return $curItem;
    }
}
