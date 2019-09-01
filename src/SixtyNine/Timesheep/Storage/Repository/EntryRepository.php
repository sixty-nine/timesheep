<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use DateTimeImmutable;
use DateTime;
use Doctrine\ORM\EntityRepository;
use SixtyNine\Timesheep\Storage\Entity\Entry;

class EntryRepository extends EntityRepository
{
    public function getDuration(DateTimeImmutable $from = null, DateTimeImmutable $to = null)
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

        if ($from) {
            $where[] = "start >= '%s'";
            $params[] = $from->format('Y-m-d h:i:s');
        }
        if ($to) {
            $where[] = "end >= '%s'";
            $params[] = $to->format('Y-m-d h:i:s');
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
     * @param DateTime|DateTimeImmutable|null $from
     * @param DateTime|DateTimeImmutable|null $to
     * @return mixed
     */
    public function getAllEntries($from = null, $to = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->orderBy('e.start')
        ;
        if ($from) {
            $qb->andWhere('e.start >= :from')->setParameter('from', $from->setTime(0, 0));
        }
        if ($to) {
            $qb->andWhere('e.end <= :to')->setParameter('to', $to->setTime(23, 59, 59)->modify('+1 second'));
        }
        return $qb->getQuery()->execute();
    }

    public function create(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        string $project = '',
        string $task = '',
        string $description = ''
    ): Entry {
        $crossingEntries = $this->findCrossingEntries($start, $end);
        if (0 < count($crossingEntries)) {
            throw new \InvalidArgumentException('Overlapping entry');
        }

        $entry = new Entry();
        $entry
            ->setStart($start)
            ->setEnd($end)
            ->setProject($project)
            ->setTask($task)
            ->setDescription($description)
        ;
        $this->_em->persist($entry);
        $this->_em->flush();

        return $entry;
    }

    public function findEntry(DateTimeImmutable $start, DateTimeImmutable $end)
    {
        return $this
            ->createQueryBuilder('e')
            ->orWhere('e.start = :start AND e.end = :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findCrossingEntries(DateTimeImmutable $start, DateTimeImmutable $end)
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.start < :start AND e.end > :start')   // start is inside another entry
            ->orWhere('e.start < :end AND e.end > :end')     // end is inside another entry
            ->orWhere(':start < e.start AND :end > e.start') // other start is inside new
            ->orWhere(':start < e.end AND :end > e.end')     // other end is inside new
            ->orWhere('e.start = :start OR e.end = :end')    // it's the same entry
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->execute()
        ;
    }
}
