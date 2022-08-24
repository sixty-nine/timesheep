<?php

use Behat\Gherkin\Node\TableNode;
use SixtyNine\Timesheep\Console\Application;
use SixtyNine\Timesheep\Helper\Objects;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Webmozart\Assert\Assert;

trait RunningCommandsTrait
{
    /** @var int */
    private $lastCommandStatus = 0;
    private $lastCommandOutput = '';

    /**
     * @When /^I call the "([^"]*)" command$/
     */
    public function iCallTheCommand(string $name)
    {
        $this->callCommandWith($name);
    }
    /**
     * @When /^I call the "([^"]*)" command with (\{[^\}]*\})$/
     */
    public function iCallTheCommandWithJson(string $name, string $json)
    {
        $this->callCommandWith($name, json_decode($json, true));
    }

    /**
     * @When /^I call the "([^"]*)" command with:$/
     */
    public function iCallTheCommandWith(string $name, TableNode $table)
    {
        $this->callCommandWith($name, array_reduce($table->getRows(), static function ($prev, $item) {
            return array_merge($prev, [$item[0] => $item[1]]);
        }, []));
    }

    /**
     * @Then /^the command should (succeed|fail)$/
     */
    public function theCommandShould($status)
    {
        if ('succeed' === $status) {
            Assert::eq(0, $this->lastCommandStatus);
        } else {
            Assert::true(0 !== $this->lastCommandStatus);
        }
    }

    /**
     * @Then /^show last command output$/
     */
    public function showLastCommandOutput()
    {
        echo $this->lastCommandOutput;
    }

    protected function callCommandWith(string $name, array $args = []): CommandTester
    {
        $app = new Application();
        $command = $app->find($name);
        if (Objects::implements($command, ContainerAwareInterface::class)) {
            /** @var BaseCommand & ContainerAwareInterface $command */
            $command->setContainer($this->container);
        }
        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge(['command' => $name], $args));
        $this->lastCommandStatus = $commandTester->getStatusCode();
        $this->lastCommandOutput = $commandTester->getDisplay();
        return $commandTester;
    }
}
