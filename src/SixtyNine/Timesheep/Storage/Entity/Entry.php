<?php

namespace SixtyNine\Timesheep\Storage\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use SixtyNine\Timesheep\Model\Period;

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
     * @var ?DateTimeImmutable
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
     * @param string $format
     * @return string
     */
    public function getStartFormatted(string $format): string
    {
        return $this->start->format($format);
    }

    /**
     * @param DateTimeImmutable $start
     * @return Entry
     */
    public function setStart(?DateTimeImmutable $start): Entry
    {
        if (null === $start) {
            throw new \InvalidArgumentException('Start cannot be null');
        }

        $this->start = $start;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getEndFormatted(string $format): string
    {
        return $this->end ? $this->end->format($format) : '-';
    }

    /**
     * @param DateTimeImmutable|null $end
     * @return Entry
     */
    public function setEnd(?DateTimeImmutable $end): Entry
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

    public function getPeriod(): Period
    {
        return new Period($this->start, $this->end);
    }

    public function __toString()
    {
        return sprintf(
            '%s-%s %s %s',
            $this->getStartFormatted('H:i'),
            $this->getEndFormatted('H:i'),
            $this->getProject(),
            $this->getTask()
        );
    }
}
