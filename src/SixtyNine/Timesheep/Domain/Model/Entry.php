<?php

namespace SixtyNine\Timesheep\Domain\Model;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

class Entry
{
    /** @var ?Project */
    protected $project;
    /** @var ?DateTimeImmutable */
    protected $start;
    /** @var ?DateTimeImmutable */
    protected $end;
    /** @var ?string */
    protected $description;

    /**
     * Entry constructor.
     * @param Project|null $project
     * @param DateTimeImmutable|null $start
     * @param DateTimeImmutable|null $end
     * @param string|null $description
     */
    public function __construct(
        ?Project $project = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
        ?string $description = null
    ) {
        $this->project = $project;
        $this->start = $start;
        $this->end = $end;
        $this->description = $description;
    }

    /**
     * @return Project
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return Entry
     */
    public function setProject(Project $project): Entry
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStart(): ?DateTimeImmutable
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
    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @param DateTimeImmutable $end
     * @return Entry
     */
    public function setEnd(DateTimeImmutable $end): Entry
    {
        if ($end >= $this->start) {
            $this->end = $end;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
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
     * @return int
     */
    public function getDuration(): int
    {

        if (!$this->end || !$this->start) {
            return 0;
        }

        return (int)$this
            ->end
            ->diff($this->start, true)
            ->format('%h');
    }
}
