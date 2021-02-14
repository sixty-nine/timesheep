<?php

namespace SixtyNine\Timesheep\Storage\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use SixtyNine\Timesheep\Storage\Entity\Project;

class ProjectRepository extends EntityRepository
{
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('p')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function exists(string $name): bool
    {
        return (bool)$this->findOneBy(['name' => Project::normalizeName($name)]);
    }

    public function create(string $name): Project
    {
        $proj = new Project(Project::normalizeName($name));
        $this->getEntityManager()->persist($proj);
        $this->getEntityManager()->flush($proj);
        return $proj;
    }

    public function delete(string $name): void
    {
        $proj = $this->findOneBy(['name' => Project::normalizeName($name)]);

        if ($proj) {
            $this->getEntityManager()->remove($proj);
            $this->getEntityManager()->flush();
        }
    }


    public function findDuplicates(): array
    {
        $visited = [];
        $duplicated = [];
        $all = $this->createQueryBuilder('p')->getQuery()->getResult();

        /** @var Project $project */
        foreach ($all as $project) {
            $normalizedName = $project->getNormalizedName();

            if (array_key_exists($normalizedName, $visited)) {
                if (!array_key_exists($normalizedName, $duplicated)) {
                    $duplicated[$normalizedName] = [
                        $visited[$normalizedName]->toArray()
                    ];
                }

                $duplicated[$normalizedName][] = $project->toArray();
            }

            $visited[$normalizedName] = $project;
        }

        return $duplicated;
    }
}
