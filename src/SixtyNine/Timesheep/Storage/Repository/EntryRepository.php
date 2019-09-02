<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use Webmozart\Assert\Assert;

class EntryRepository extends EntityRepository
{
    public function getDuration(Period $period)
    {
        $sql = <<<SQL
select
    cast(
        round(sum(julianday(end) - julianday(start)) * 24, 2) as FLOAT
    ) as duration
    from entries
SQL;

        $where = [];
        $params = [];

        if ($start = $period->getStart()) {
            $where[] = "start >= '%s'";
            $params[] = $start->format('Y-m-d h:i:s');
        }
        if ($end = $period->getEnd()) {
            $where[] = "end >= '%s'";
            $params[] = $end->format('Y-m-d h:i:s');
        }
        if ($where) {
            $sql .= ' where '.implode(' AND ', $where);
        }
        $sql = sprintf($sql, ...$params);
        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();
        $res = reset($res);
        return (float)$res['duration'];
    }

    /**
     * @param Period $period
     * @return mixed
     */
    public function getAllEntries(Period $period)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->orderBy('e.start')
        ;
        /** @var DateTimeImmutable $start */
        if ($start = $period->getStart()) {
            $qb
                ->andWhere('e.start >= :from')
                ->setParameter('from', $start->setTime(0, 0))
            ;
        }
        /** @var DateTimeImmutable $end */
        if ($end = $period->getEnd()) {
            $qb
                ->andWhere('e.end <= :to')
                ->setParameter('to', $end->setTime(23, 59, 59)->modify('+1 second'))
            ;
        }
        return $qb->getQuery()->execute();
    }

    public function create(
        Period $period,
        string $project = '',
        string $task = '',
        string $description = ''
    ): Entry {
        $crossingEntries = $this->findCrossingEntries($period);
        if (0 < count($crossingEntries)) {
            throw new \InvalidArgumentException('Overlapping entry');
        }

        /** @var DateTimeImmutable $start */
        $start = $period->getStart();
        Assert::notNull($start, 'An entry must have a start date');

        $entry = new Entry();
        $entry
            ->setStart($start)
            ->setEnd($period->getEnd())
            ->setProject($project)
            ->setTask($task)
            ->setDescription($description)
        ;
        $this->_em->persist($entry);
        $this->_em->flush();

        return $entry;
    }

    public function findEntry(Period $period)
    {
        return $this
            ->createQueryBuilder('e')
            ->orWhere('e.start = :start AND e.end = :end')
            ->setParameter('start', $period->getStart())
            ->setParameter('end', $period->getEnd())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findCrossingEntries(Period $period)
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.start < :start AND e.end > :start')   // start is inside another entry
            ->orWhere('e.start < :end AND e.end > :end')     // end is inside another entry
            ->orWhere(':start < e.start AND :end > e.start') // other start is inside new
            ->orWhere(':start < e.end AND :end > e.end')     // other end is inside new
            ->orWhere('e.start = :start OR e.end = :end')    // it's the same entry
            ->setParameter('start', $period->getStart())
            ->setParameter('end', $period->getEnd())
            ->getQuery()
            ->execute()
        ;
    }
}
