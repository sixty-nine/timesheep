<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use SixtyNine\Timesheep\Storage\Entity\Entry;

class EntryRepository extends EntityRepository
{
    public function getAllEntries(DateTimeImmutable $from = null, DateTimeImmutable $to = null)
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
            ->where('e.start < :start AND e.end > :start')  // start is inside another entry
            ->orWhere('e.start < :end AND e.end > :end')    // end is inside another entry
            ->orWhere('e.start = :start OR e.end = :end')   // it's the same entry
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->execute()
        ;
    }
}
