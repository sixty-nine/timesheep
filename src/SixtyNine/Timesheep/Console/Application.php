<?php

namespace SixtyNine\Timesheep\Console;

use RuntimeException;
use Phar;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use SixtyNine\Timesheep\Bootstrap;
use SixtyNine\Timesheep\Console\Command;
use SixtyNine\Timesheep\Helper\Objects;
use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApp
{
    const VERSION = '1.0.0-rc1';
    const LOGO = "\xF0\x9F\x90\x91";
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct('TimeSheep '.self::LOGO, self::VERSION);

        $this->logger = $logger ?: new NullLogger();
        $this->logger->log(LogLevel::INFO, 'Timesheep application started');

        $this->addCommands([
            new Command\Project\ListProjectsCommand(),
            new Command\Project\AddProjectCommand(),
            new Command\Project\RemoveProjectCommand(),
            new Command\Project\FindDuplicatedProjectsCommand(),

            new Command\Entry\ListEntriesCommand(),
            new Command\Entry\AddEntryCommand(),
            new Command\Entry\EditEntryCommand(),
            new Command\Entry\DeleteEntryCommand(),

            new Command\Stats\EntriesStatsCommand(),
            new Command\Stats\ProjectStatsCommand(),
            new Command\Stats\PresenceStatsCommand(),

            new Command\Database\ArchiveDbCommand(),
            new Command\Database\BackupDbCommand(),
            new Command\Database\DbInfoCommand(),

            new Command\CalendarCommand(),
        ]);

        if (Phar::running() === '') {
            $this->addCommands([
                new Command\CreatePharCommand(),
            ]);
        }
    }

    protected function doRunCommand(BaseCommand $command, InputInterface $input, OutputInterface $output)
    {
        $input = $input ?? new ArgvInput();
        $output = $output ?? new ConsoleOutput();

        $configFile = dirname(__DIR__, 4) . '/timesheep.yml';

        if ($input->getOption('config')) {
            $configFile = $input->getOption('config');
        }

        if (!file_exists($configFile)) {
            throw new RuntimeException('Config file not found: ' . $configFile);
        }
        $container = Bootstrap::boostrap($this->logger, $configFile);

        if (Objects::implements($command, ContainerAwareInterface::class)) {
            /** @var BaseCommand & ContainerAwareInterface $command */
            $command->setContainer($container);
        }

        return parent::doRunCommand($command, $input, $output);
    }

    /**
     * @param BaseCommand $command
     * @return BaseCommand|null
     */
    public function add(BaseCommand $command)
    {
        if (Objects::implements($command, LoggerAwareInterface::class)) {
            /** @var BaseCommand & LoggerAwareInterface $command */
            $command->setLogger($this->logger);
        }

        return parent::add($command);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption('config', 'c', InputArgument::OPTIONAL, 'Path to the config file')
        );

        return $definition;
    }
}
