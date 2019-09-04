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
        Arrays::setValueDotted($this->config, 'db.url', getenv('TIMESHEEP_DB_URL'));
        Arrays::setValueDotted($this->config, 'console.box-style', getenv('BOX_STYLE') ?: 'default');
    }

    public function get(string $key): string
    {
        return Arrays::getValueDotted($this->config, $key) ?? '';
    }
}
