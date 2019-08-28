<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use SixtyNine\Timesheep\Storage\Entity\Project;

class ProjectRepository extends EntityRepository
{
    public function findAll()
    {
        return $this
            ->createQueryBuilder('p')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function exists(string $name): bool
    {
        return (bool)$this->findOneBy(['name' => $name]);
    }

    public function create(string $name): Project
    {
        $proj = new Project($name);
        $this->getEntityManager()->persist($proj);
        $this->getEntityManager()->flush($proj);
        return $proj;
    }

    public function delete(string $name): void
    {
        $proj = $this->findOneBy(['name' => $name]);

        if ($proj) {
            $this->getEntityManager()->remove($proj);
            $this->getEntityManager()->flush();
        }
    }
}
