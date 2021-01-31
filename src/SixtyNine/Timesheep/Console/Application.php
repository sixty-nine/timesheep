<?php

namespace SixtyNine\Timesheep\Console;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use SixtyNine\Timesheep\Helper\Objects;
use SixtyNine\Timesheep\Console\Command;
use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApp
{
    const LOGO = "\xF0\x9F\x90\x91";
    /** @var ContainerInterface */
    private $container;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        parent::__construct('TimeSheep '.self::LOGO, '0.0.0');
        $this->logger = $logger ?: new NullLogger();
        $this->container = $container;

        $this->logger->log(LogLevel::INFO, 'Timesheep application started');

        $this->addCommands([
            new Command\Project\ListProjectsCommand(),
            new Command\Project\AddProjectCommand(),
            new Command\Project\RemoveProjectCommand(),
            new Command\Entry\ListEntriesCommand(),
            new Command\Entry\AddEntryCommand(),
            new Command\Entry\EditEntryCommand(),
            new Command\Entry\DeleteEntryCommand(),
            new Command\Database\ArchiveDbCommand(),
            new Command\Database\BackupDbCommand(),
            new Command\Database\DbInfoCommand(),
        ]);
    }

    /**
     * @param BaseCommand $command
     * @return BaseCommand|null
     */
    public function add(BaseCommand $command)
    {
        if (Objects::implements($command, LoggerAwareInterface::class)) {
            /** @var LoggerAwareInterface $command */
            $command->setLogger($this->logger);
        }

        if (Objects::implements($command, ContainerAwareInterface::class)) {
            /** @var ContainerAwareInterface $command */
            $command->setContainer($this->container);
        }

        return parent::add($command);
    }
}
