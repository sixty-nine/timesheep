<?php

namespace SixtyNine\Timesheep;

use SixtyNine\Timesheep\Config\TimesheepConfiguration;
use SixtyNine\Timesheep\Helper\Arrays;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /** @var array<mixed> */
    protected $config = [];

    public function __construct(string $configFile)
    {
        $isConfigFile = file_exists($configFile) &&
            is_writable($configFile) &&
            !is_dir($configFile);

        if (!$isConfigFile) {
            die(sprintf("Invalid config file: %s\n", $configFile));
        }

        /** @var string $content */
        $content = file_get_contents($configFile);
        $config = Yaml::parse($content);

        $processor = new Processor();
        $this->config = $processor->processConfiguration(
            new TimesheepConfiguration(),
            $config
        );

        $this->config += [
            'config_file' => realpath($configFile),
            'datetime_format' => $this->config['date_format'] . ' ' . $this->config['time_format'],
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
