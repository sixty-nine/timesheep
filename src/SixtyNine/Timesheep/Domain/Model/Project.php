<?php

namespace SixtyNine\Timesheep\Domain\Model;

class Project
{
    /** @var ?string */
    protected $name;

    /**
     * Project constructor.
     * @param string|null $name
     */
    public function __construct(?string $name)
    {
        $this->name = $name;
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
     * @return Project
     */
    public function setName(string $name): Project
    {
        $this->name = $name;
        return $this;
    }
}
