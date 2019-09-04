<?php

namespace SixtyNine\Timesheep\Model;

use Webmozart\Assert\Assert;

class ProjectStatistics
{
    /** @var array */
    private $hoursPerProject = [];
    /** @var int */
    private $total = 0;


    /**
     * @return int
     */
    public function getProjectHours(string $name): int
    {
        if (!array_key_exists($name, $this->hoursPerProject)) {
            return 0;
        }
        return $this->hoursPerProject[$name];
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return ProjectStatistics
     */
    public function setTotal(int $total): ProjectStatistics
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @param string $name
     * @param int $hours
     */
    public function addProjectHours(string $name, int $hours): void
    {
        if (!array_key_exists($name, $this->hoursPerProject)) {
            $this->hoursPerProject[$name] = 0;
        }
        $this->hoursPerProject[$name] += $hours;
    }
}
