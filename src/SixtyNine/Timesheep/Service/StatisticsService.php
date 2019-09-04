<?php

namespace SixtyNine\Timesheep\Service;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Model\ProjectStatistics;

class StatisticsService
{
    /** @var EntityManager */
    private $em;

    /**
     * StatisticsService constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getTotalDuration(Period $period = null): int
    {
        $sql = <<<SQL
select
    cast(
        round(sum(julianday(end) - julianday(start)) * 24, 2) as FLOAT
    ) as duration
    from entries
SQL;

        $sql = $this->rawSqlWithPeriod($sql, $period);

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();
        $res = reset($res);
        return (int)$res['duration'];
    }

    public function getProjectStats(Period $period = null): ProjectStatistics
    {
        $sql = <<<SQL
select
    project,
    round(sum(julianday(end) - julianday(start)) * 24, 2) as total
from entries
SQL;

        $sql = $this->rawSqlWithPeriod($sql, $period);
        $sql .= ' group by project';

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        $stats = new ProjectStatistics();
        $stats->setTotal($this->getTotalDuration($period));

        foreach ($res as $item) {
            $stats->addProjectHours($item['project'], $item['total']);
        }

        return $stats;
    }

    public function rawSqlWithPeriod(string $sql, Period $period = null): string
    {
        $where = [];
        $params = [];

        if ($period) {
            if ($start = $period->getStart()) {
                $where[] = "start >= '%s'";
                $params[] = $start->format('Y-m-d 00:00:00');
            }
            if ($end = $period->getEnd()) {
                $where[] = "end <= '%s'";
                $params[] = $end->format('Y-m-d 23:59:59');
            }
        }
        if ($where) {
            $sql .= ' where '.implode(' AND ', $where);
        }

        return sprintf($sql, ...$params);
    }
}
