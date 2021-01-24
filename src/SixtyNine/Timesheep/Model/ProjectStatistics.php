<?php

namespace SixtyNine\Timesheep\Model;

use SixtyNine\Timesheep\Helper\DateTimeHelper;

class ProjectStatistics
{
    /** @var array */
    private $hoursPerProject = [];
    /** @var float */
    private $total = 0;


    /**
     * @param string $name
     * @return float
     */
    public function getProjectHours(string $name): float
    {
        if (!array_key_exists($name, $this->hoursPerProject)) {
            return 0;
        }
        return $this->hoursPerProject[$name];
    }

    /**
     * @return array
     */
    public function getProjectsHours(): array
    {
        return $this->hoursPerProject;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    public function getDataTable(DateTimeHelper $dtHelper): DataTable
    {
        $table = new DataTable(['Project', 'Duration', '']);

        foreach ($this->hoursPerProject as $project => $hours) {
            $table->addRow([
                $project,
                sprintf('%sh', $hours), $dtHelper->decimalToTime($hours)
            ]);
        }

        return $table;
    }

    /**
     * @param string $name
     * @param float $hours
     */
    public function addProjectHours(string $name, float $hours): void
    {
        if (!array_key_exists($name, $this->hoursPerProject)) {
            $this->hoursPerProject[$name] = 0;
        }
        $this->total += $hours;
        $this->hoursPerProject[$name] += $hours;
    }
}
