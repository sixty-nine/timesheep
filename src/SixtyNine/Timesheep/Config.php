<?php

namespace SixtyNine\Timesheep;

use SixtyNine\Timesheep\Helper\Arrays;

class Config
{
    protected $config = [];

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $dateFormat = getenv('DATE_FORMAT');
        $timeFormat = getenv('TIME_FORMAT');
        Arrays::setValueDotted($this->config, 'db.url', getenv('TIMESHEEP_DB_URL'));
        Arrays::setValueDotted($this->config, 'console.box-style', getenv('BOX_STYLE') ?: 'default');
        Arrays::setValueDotted($this->config, 'time.rounding', getenv('TIME_ROUNDING') ?: '5');
        Arrays::setValueDotted($this->config, 'format.date', $dateFormat ?: 'd-m-Y');
        Arrays::setValueDotted($this->config, 'format.time', $timeFormat ?: 'H:i');
        Arrays::setValueDotted(
            $this->config,
            'format.datetime',
            $dateFormat && $timeFormat ? $dateFormat.' '.$timeFormat: 'd-m-Y H:i'
        );
    }

    public function get(string $key): string
    {
        return Arrays::getValueDotted($this->config, $key) ?? '';
    }

    public function toArray(): array
    {
        return [
            'db' => ['url' => $this->get('db.url')],
            'console' => ['box-style' => $this->get('console.box-style')],
            'time' => ['rounding' => $this->get('time.rounding')],
            'format' => [
                'date' => $this->get('format.date'),
                'time' => $this->get('format.time'),
                'datetime' => $this->get('format.datetime'),
            ]
        ];
    }
}
