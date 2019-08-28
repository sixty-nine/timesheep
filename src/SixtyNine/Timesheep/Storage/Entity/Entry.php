<?php

namespace SixtyNine\Timesheep\Storage\Entity;

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
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $start;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected $end;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;
}
