<?php

namespace SixtyNine\Timesheep\Storage\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SixtyNine\Timesheep\Storage\Repository\EntryRepository")
 * @ORM\Table(name="entries")
 **/
class Entry
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $start;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected $end;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $project;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $task;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * @param DateTimeImmutable $start
     * @return Entry
     */
    public function setStart(DateTimeImmutable $start): Entry
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @param DateTimeImmutable $end
     * @return Entry
     */
    public function setEnd(DateTimeImmutable $end): Entry
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return string
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @param string $project
     * @return Entry
     */
    public function setProject(string $project): Entry
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return string
     */
    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * @param string $task
     * @return Entry
     */
    public function setTask(string $task): Entry
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Entry
     */
    public function setDescription(string $description): Entry
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the duration in hours
     * @return string
     */
    public function getDuration(): string
    {
        if (null === $this->end || null === $this->start) {
            return '00:00';
        }

        return $this
            ->end
            ->diff($this->start, true)
            ->format('%H:%I');
    }

    /**
     * Get the decimal duration.
     * @return float
     */
    public function getDecimalDuration(): float
    {
        if (null === $this->end || null === $this->start) {
            return 0;
        }

        $diff = $this->end->diff($this->start, true);
        return $diff->h + round($diff->i / 60, 2);
    }
}
