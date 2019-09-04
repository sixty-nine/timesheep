<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use SixtyNine\Timesheep\Bootstrap;
use SixtyNine\Timesheep\Helper\Doctrine as DoctrineHelper;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Model\ProjectStatistics;
use SixtyNine\Timesheep\Service\StatisticsService;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Webmozart\Assert\Assert;

class FeatureContext implements Context
{
    use CastingDateTrait;
    use CastingTimeToDateTrait;
    use CastingTimesToPeriodTrait;
    use RunningCommandsTrait;

    /** @var EntityManager */
    private $em;
    /** @var EntryRepository $entryRepo */
    private $entryRepo;
    /** @var ProjectRepository $projRepo */
    private $projRepo;
    /** @var ContainerInterface */
    private $container;
    /** @var ProjectStatistics */
    private $stats;

    public function __construct()
    {
        $this->container = Bootstrap::boostrap();
        $this->em = $this->container->get('em');
        $this->entryRepo = $this->em->getRepository(Entry::class);
        $this->projRepo = $this->em->getRepository(Project::class);
    }

    /**
     * @Given /^I have an empty database$/
     * @Given /^my timesheet is empty$/
     */
    public function iHaveAnEmptyDatabase()
    {
        DoctrineHelper::truncateAll($this->em->getConnection());
    }

    /**
     * @Given /^I have an entry (.*)$/
     * @Given /^I should be able to create an entry (.*)$/
     */
    public function iHaveAnEntryFromTo(Period $period)
    {
        $entry = $this->entryRepo->create($period);
    }

    /**
     * @Then /^I should not be able to create an entry (.*)$/
     */
    public function iShouldNotBeAbleToCreateAnEntryFromTo(Period $period)
    {
        try {
            $thrown = false;
            $this->entryRepo->create($period);
        } catch (\Exception $ex) {
            $thrown = true;
        }

        Assert::true($thrown, 'The entry could be created');
    }

    /**
     * @Then /^I should have an?(?: new)? entry (from .* to [^ ]*)$/
     * @Then /^I should have an?(?: new)? entry (from .* to [^ ]* on [^ ])$/
     * @Then /^I should have an?(?: new)? entry (from .* to [^ ]* on [^ ]) in project (.*)$/
     * @Then /^I should have an?(?: new)? entry (.*) in project (.*)$/
     */
    public function iShouldHaveANewEntryFromTo(Period $period, string $project = null)
    {
        $entry = $this->entryRepo->findEntry($period, $project);
        Assert::notNull($entry, 'Entry not found');
    }

    /**
     * @Given /^I should have (\d+) entr(?:y|ies)$/
     */
    public function iShouldHaveEntries(int $number)
    {
        Assert::count($this->entryRepo->findAll(), $number);
    }

    /**
     * @Then /^I should have a project (.*)$/
     */
    public function iShouldHaveAProjectPROJ(string $project)
    {
        Assert::true($this->projRepo->exists($project));
    }

    /**
     * @Given /^I should have (?:only )?(\d+) projects?$/
     */
    public function iShouldHaveProject($count)
    {
        Assert::eq($count, $this->projRepo->count([]));
    }

    /**
     * @When /^I request the stats(?: for (.*))?$/
     */
    public function iRequestTheStats(string $date = null)
    {
        $period = new Period();
        if ($date) {
            $period->setStart(new DateTimeImmutable($date));
            $period->setEnd(new DateTimeImmutable($date));
        }
        $service = new StatisticsService($this->em);
        $this->stats = $service->getProjectStats($period);
    }

    /**
     * @Then /^I should have (\d+) hours? in (.*)/
     */
    public function iShouldHaveHoursInProject($hours, $project)
    {
        Assert::eq($hours, $this->stats->getProjectHours($project));
    }

    /**
     * @Given /^the total should be (\d+) hours?$/
     */
    public function theTotalShouldBeHours($hours)
    {
        Assert::eq($hours, $this->stats->getTotal());
    }
}
