<?php

namespace SixtyNine\Timesheep\Console;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use SixtyNine\Timesheep\Config;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TimesheepCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var EntityManager */
    protected $em;
    /** @var EntryRepository */
    protected $entriesRepo;
    /** @var ProjectRepository */
    protected $projectRepo;
    /** @var Config */
    protected $config;

    public function setContainer(ContainerInterface $container = null): void
    {
        if (null === $container) {
            throw new InvalidArgumentException('No container');
        }

        $this->container = $container;

        // Assigning to variables is required to make phpstan happy, do not refactor !!

        /** @var EntityManager $em */
        $em = $this->container->get('em');
        $this->em = $em;

        /** @var EntryRepository $repo */
        $repo = $this->em->getRepository(Entry::class);
        $this->entriesRepo = $repo;

        /** @var PRojectRepository $repo */
        $repo = $this->em->getRepository(Project::class);
        $this->projectRepo = $repo;

        /** @var Config $config */
        $config = $this->container->get('config');
        $this->config = $config;
    }

    protected function inputTime(InputInterface $input, SymfonyStyle $io): Period
    {
        $force = $input->getOption('force');
        $start = $input->getArgument('start');
        $end = $input->getArgument('end');

        if (!$force && !$start) {
            $start = $io->ask('Start time');
        }
        if (!$force && !$end) {
            $end = $io->ask('End time');
        }

        return Period::fromString($start, $end);
    }

    protected function inputProject(
        InputInterface $input,
        OutputInterface $output,
        EntityManager $em
    ): string {
        $projRepo = $em->getRepository(Project::class);
        $helper = $this->getHelper('question');

        $question = new Question(" <info>Project name:</info>\n >", '');
        $projects = $projRepo->findAll();
        $question->setAutocompleterCallback(
            static function (string $userInput) use ($projects): array {
                $list = $projects;
                $filter = static function ($project) use ($userInput) {
                    return strpos($project['name'], $userInput) === 0;
                };
                $map = static function ($project) {
                    return $project['name'];
                };

                if ($userInput) {
                    $list = array_filter($projects, $filter);
                }

                return array_values(array_map($map, $list));
            }
        );

        $res = $helper->ask($input, $output, $question);
        $output->writeln('');
        return $res;
    }

    protected function getPeriodFromParams(InputInterface $input): Period
    {
        /** @var string $fromStr */
        $fromStr = $input->getOption('from');
        /** @var string $toStr */
        $toStr = $input->getOption('to');
        $period = new Period(
            $fromStr ? new DateTimeImmutable($fromStr) : null,
            $toStr ? new DateTimeImmutable($toStr) : null
        );

        if ($input->getOption('week')) {
            return Period::getWeek($period->getFirstDateOrToday());
        }

        if ($input->getOption('month')) {
            return Period::getMonth($period->getFirstDateOrToday());
        }

        if ($input->getOption('day')) {
            $day = $period->getFirstDateOrToday();
            return new Period($day, $day);
        }

        return $period;
    }
}
