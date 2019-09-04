<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AddEntryCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'entry:add';

    protected function configure()
    {
        $this
            ->setDescription('Add a new entry.')
            ->setAliases(['e:add', 'add'])
            ->addArgument('start', InputArgument::OPTIONAL, 'Start date/time.')
            ->addArgument('end', InputArgument::OPTIONAL, 'End date/time.')
            ->addArgument('project', InputArgument::OPTIONAL, 'Project code.')
            ->addArgument('task', InputArgument::OPTIONAL, 'Task, i.e. JIRA ticket or such.')
            ->addArgument('description', InputArgument::OPTIONAL, 'Optional description')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the removal.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var EntityManager $em */
        $em = $this->container->get('em');
        /** @var EntryRepository $repo */
        $repo = $em->getRepository(Entry::class);

        /** @var string $force */
        $force = $input->getOption('force');
        /** @var string $project */
        $project = $input->getArgument('project');
        /** @var string $task */
        $task = $input->getArgument('task');
        /** @var string $description */
        $description = $input->getArgument('description');

        $io = new SymfonyStyle($input, $output);
        $io->title('Add a new entry');

        $period = $this->inputTime($input, $io);

        if (!$force && !$project) {
            $project = $this->inputProject($input, $output, $em);
        }

        if (!$force && !$task) {
            $task = $io->ask('Task');
        }

        if (!$force && !$description) {
            $task = $io->ask('Description');
        }

        $io->writeln([
            sprintf('Start: <info>%s</info>', $period->getStartFormatted('Y-m-d H:i:s')),
            sprintf('End: <info>%s</info>', $period->getEndFormatted('Y-m-d H:i:s')),
            sprintf('Duration: <info>%s</info>', $period->getDurationString()),
            sprintf('Decimal: <info>%s</info>', $period->getDuration()),
            sprintf('Project: <info>%s</info>', $project),
            sprintf('Task: <info>%s</info>', $task),
            sprintf('Description: <info>%s</info>', $description),
        ]);

        if ($entry = $this->checkNoCrossingEntries($repo, $period)) {
            $io->error(['There is another entry crossing this one', $entry]);
            return 1;
        }

        if (!$force && !$io->confirm('Is it correct?', false)) {
            $io->writeln('Aborted.');
            return 1;
        }

        $repo->create($period, $project ?? '', $task ?? '', $description ?? '');
        $io->writeln('Entry created');
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

    /**
     * @param EntryRepository $repo
     * @param Period $period
     * @return bool|Entry
     */
    protected function checkNoCrossingEntries(EntryRepository $repo, Period $period)
    {
        $crossingEntries = $repo->findCrossingEntries($period);
        if (0 < count($crossingEntries)) {
            /** @var Entry $firstEntry */
            return $crossingEntries[0];
        }

        return false;
    }
}
