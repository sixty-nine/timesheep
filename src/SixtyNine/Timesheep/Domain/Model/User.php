<?php

namespace SixtyNine\Timesheep\Domain\Model;

class User
{
    /** @var ?string */
    protected $name;
    /**
     * How many hours are due weekly
     * @var int
     */
    protected $weeklyDueHours;

    /**
     * User constructor.
     * @param string $name
     * @param int $weeklyDueHours
     */
    public function __construct(?string $name, int $weeklyDueHours = 0)
    {
        $this->name = $name;
        $this->weeklyDueHours = $weeklyDueHours;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeeklyDueHours(): int
    {
        return $this->weeklyDueHours;
    }

    /**
     * @param int $weeklyDueHours
     * @return User
     */
    public function setWeeklyDueHours(int $weeklyDueHours): User
    {
        $this->weeklyDueHours = $weeklyDueHours;
        return $this;
    }
}
