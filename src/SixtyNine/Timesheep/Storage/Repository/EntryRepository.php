<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use \RuntimeException;
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

    public function saveEntry(Entry $entry): void
    {
        $this->_em->persist($entry);
        $this->_em->flush();
    }

    public function create(
        Period $period,
        string $project = '',
        string $task = '',
        string $description = ''
    ): Entry {
        $crossingEntries = $this->findCrossingEntries($period);
        if (0 < count($crossingEntries)) {
            throw new InvalidArgumentException('Overlapping entry');
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

        // Create entry or merge it with adjacent similar entries.

        $entryBefore = null;
        $entryAfter = null;

        if (null !== $period->getStart()) {
            $entryBefore = $this->findEntryEndingAt($period->getStart(), $project, $task);
        }

        if (null !== $period->getEnd()) {
            $entryAfter = $this->findEntryStartingAt($period->getEnd(), $project, $task);
        }

        if ($entryBefore && $entryAfter) {
            $entryBefore->setEnd($entryAfter->getEnd());
            $this->deleteEntry($entryAfter->getId());
            $this->saveEntry($entryBefore);
            return $entryBefore;
        } elseif ($entryBefore) {
            $entryBefore->setEnd($period->getEnd());
            $this->saveEntry($entryBefore);
            return $entryBefore;
        } elseif ($entryAfter) {
            $entryAfter->setStart($period->getStart());
            $this->saveEntry($entryAfter);
            return $entryAfter;
        }

        $entry = new Entry();
        $entry
            ->setStart($start)
            ->setEnd($period->getEnd())
            ->setProject(Project::normalizeName($project))
            ->setTask($task)
            ->setDescription($description)
        ;
        $this->_em->persist($entry);
        $this->_em->flush();

        return $entry;
    }

    public function editEntry(
        int $id,
        Period $period,
        string $project = '',
        string $task = '',
        string $description = ''
    ): Entry {
        $crossingEntry = $this->checkNoCrossingEntries($period);
        if (!is_bool($crossingEntry) && $crossingEntry->getId() !== $id) {
            throw new InvalidArgumentException('Overlapping entry');
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

        /** @var Entry $entry */
        $entry = $this->find($id);
        Assert::notNull($entry, 'Cannot find entry ID ' . $id);

        $entry
            ->setStart($start)
            ->setEnd($period->getEnd())
            ->setProject(Project::normalizeName($project))
            ->setTask($task)
            ->setDescription($description)
        ;
        $this->_em->persist($entry);
        $this->_em->flush();

        return $entry;
    }

    public function deleteEntry(int $id): void
    {
        $entry = $this->find($id);

        if (!$entry) {
            throw new RuntimeException('No entry found for id ' . $id);
        }

        $this->_em->remove($entry);
        $this->_em->flush();
    }

    public function findEntry(Period $period, string $project = null): ?Entry
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

    public function findEntryStartingAt(?DateTimeImmutable $start, string $project = null, string $task = null): ?Entry
    {
        if (null === $start) {
            return null;
        }

        $qb = $this
            ->createQueryBuilder('e')
            ->andWhere('e.start = :start')
            ->setParameter('start', $start)
        ;

        if ($project) {
            $qb
                ->andWhere('e.project = :project')
                ->setParameter('project', $project)
            ;
        }

        if ($task) {
            $qb
                ->andWhere('e.task = :task')
                ->setParameter('task', $task)
            ;
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findEntryEndingAt(?DateTimeImmutable $end, string $project = null, string $task = null): ?Entry
    {
        if (null === $end) {
            return null;
        }

        $qb = $this
            ->createQueryBuilder('e')
            ->andWhere('e.end = :start')
            ->setParameter('start', $end)
        ;

        if ($project) {
            $qb
                ->andWhere('e.project = :project')
                ->setParameter('project', $project)
            ;
        }

        if ($task) {
            $qb
                ->andWhere('e.task = :task')
                ->setParameter('task', $task)
            ;
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findEntriesWithNoEndingTime(): array
    {
        return $this->findBy(['end' => null]);
    }

    public function findCrossingEntries(Period $period): array
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

    protected function getBaseQueryBuilder(Period $period = null): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->orderBy('e.start')
        ;

        if ($period) {
            if ($start = $period->getStart()) {
                $qb
                    ->andWhere('e.start >= :from')
                    ->setParameter('from', $start->setTime(0, 0))
                ;
            }

            if ($end = $period->getEnd()) {
                $qb
                    ->andWhere('e.end <= :to')
                    ->setParameter('to', $end->setTime(23, 59, 59)->modify('+1 second'))
                ;
            }
        }

        return $qb;
    }

    /**
     * @param Period $period
     * @return bool|Entry
     */
    public function checkNoCrossingEntries(Period $period)
    {
        $crossingEntries = $this->findCrossingEntries($period);
        if (0 < count($crossingEntries)) {
            return $crossingEntries[0];
        }

        return false;
    }

    public function getLastProjectUsage(string $name): ?DateTimeImmutable
    {
        $res = $this
            ->createQueryBuilder('e')
            ->andWhere('e.project = :project')
            ->setParameter('project', $name)
            ->orderBy('e.start', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->execute();

        if (!$res) {
            return null;
        }

        /** @var Entry $res */
        $res = reset($res);
        return $res->getStart();
    }
}
