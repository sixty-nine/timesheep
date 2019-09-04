<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Entity\Project;
use Webmozart\Assert\Assert;

class EntryRepository extends EntityRepository
{
    /**
     * @param Period|null $period
     * @return mixed
     */
    public function getAllEntries(Period $period = null)
    {
        $qb = $this->getBaseQueryBuilder($period);
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

        if ($project) {
            /** @var ProjectRepository $projRepo */
            $projRepo = $this->_em->getRepository(Project::class);
            if (!$projRepo->exists($project)) {
                $projRepo->create($project);
            }
        }

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

    public function findEntry(Period $period, string $project = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->andWhere('e.start = :start AND e.end = :end')
            ->setParameter('start', $period->getStart())
            ->setParameter('end', $period->getEnd())
        ;

        if ($project) {
            $qb
                ->andWhere('e.project = :project')
                ->setParameter('project', $project)
            ;
        }

        return $qb
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

    protected function getBaseQueryBuilder(Period $period = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->orderBy('e.start')
        ;

        if ($period) {
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
        }

        return $qb;
    }
}
