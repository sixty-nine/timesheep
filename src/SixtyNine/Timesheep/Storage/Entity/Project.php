<?php

namespace SixtyNine\Timesheep\Storage\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SixtyNine\Timesheep\Storage\Repository\ProjectRepository")
 * @ORM\Table(name="projects")
 **/
class Project
{
    /**
     * @var                        int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var                       string
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;

    /**
     * Project constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string $name
     * @return Project
     */
    public function setName(string $name): Project
    {
        $this->name = $name;
        return $this;
    }
}
