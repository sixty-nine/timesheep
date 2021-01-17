<?php

namespace SixtyNine\Timesheep\Console;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class TimesheepCommand extends Command implements ContainerAwareInterface
{
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
}
