<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

use Behat\Behat\Context\Context;
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
    use TimeBlocksTestingTrait;

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
        $this->container = Bootstrap::boostrap(null, 'timesheep.test.yml');
        $this->em = $this->container->get('em');
        $this->entryRepo = $this->em->getRepository(Entry::class);
        $this->projRepo = $this->em->getRepository(Project::class);
    }


    /**
     * From https://stackoverflow.com/a/33195692/643106
     * @param DateTimeImmutable $datetime
     * @param int $precision
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function roundTime(DateTimeImmutable $datetime, $precision = 30): DateTimeImmutable
    {
        if ($precision === 0) {
            return $datetime;
        }

        $dt = DateTime::createFromFormat(
            DateTimeInterface::ATOM,
            $datetime->format(DateTimeInterface::ATOM)
        );

        if (!$dt) {
            throw new \InvalidArgumentException('Invalid immutable date/time');
        }

        // 1) Set number of seconds to 0 (by rounding up to the nearest minute if necessary)
        $second = (int)$dt->format('s');
        if ($second > 30) {
            // Jumps to the next minute
            $dt->add(new DateInterval('PT' . (60 - $second) . 'S'));
        } elseif ($second > 0) {
            // Back to 0 seconds on current minute
            $dt->sub(new DateInterval('PT' . $second . 'S'));
        }
        // 2) Get minute
        $minute = (int)$dt->format('i');
        // 3) Convert modulo $precision
        $minute %= $precision;
        if ($minute > 0) {
            // 4) Count minutes to next $precision-multiple minutes
            $diff = $precision - $minute;
            // 5) Add the difference to the original date time
            $dt->add(new DateInterval('PT' . $diff . 'M'));
        }

        return DateTimeImmutable::createFromMutable($dt);
    }

    /**
     * @Given /^I have an empty database$/
     * @Given /^my timesheet is empty$/
     */
    public function iHaveAnEmptyDatabase(): void
    {
        DoctrineHelper::truncateAll($this->em->getConnection());
    }

    /**
     * @Given /^I have an entry (.*)$/
     * @Given /^I should be able to create an entry (.*)$/
     */
    public function iHaveAnEntryFromTo(Period $period): void
    {
        $this->entryRepo->create($period);
    }

    /**
     * @Then /^I should not be able to create an entry (.*)$/
     */
    public function iShouldNotBeAbleToCreateAnEntryFromTo(Period $period): void
    {
        try {
            $thrown = false;
            $this->entryRepo->create($period);
        } catch (Exception $ex) {
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
    public function iShouldHaveANewEntryFromTo(Period $period, string $project = null): void
    {
        $entry = $this->entryRepo->findEntry($period, $project);
        Assert::notNull($entry, 'Entry not found');
    }

    /**
     * @Then /^I should have an?(?: new)? entry starting at (.*)$/
     * @Then /^I should have an?(?: new)? entry starting at (.*) in project (.*)$/
     */
    public function iShouldHaveAnEntryStartingAt(DateTimeImmutable $start, string $project = null): void
    {
        $rounding = getenv('TIME_ROUNDING');
        $rounded = $this->roundTime($start, $rounding);
        $entry = $this->entryRepo->findEntryStartingAt($rounded, $project);
        Assert::notNull($entry, sprintf('No entry starting at %s found', $rounded->format(DateTimeInterface::ATOM)));
    }

    /**
     * @Then /^I should have an?(?: new)? entry starting now$/
     * @Then /^I should have an?(?: new)? entry starting now in project (.*)$/
     */
    public function iShouldHaveAnEntryStartingNow(string $project = null): void
    {
        $start = new DateTimeImmutable();
        $rounding = getenv('TIME_ROUNDING');
        $rounded = $this->roundTime($start, $rounding);
        $entry = $this->entryRepo->findEntryStartingAt($rounded, $project);
        Assert::notNull($entry, sprintf('No entry starting at %s found', $rounded->format(DateTimeInterface::ATOM)));
    }

    /**
     * @Then /^I should have (\d+) entr(?:y|ies)$/
     */
    public function iShouldHaveEntries(int $number): void
    {
        Assert::count($this->entryRepo->findAll(), $number);
    }

    /**
     * @Then /^I should have (\d+) entr(?:y|ies) with no ending time$/
     */
    public function iShouldHaveEntriesWithNoEndingTime(int $number): void
    {
        Assert::count($this->entryRepo->findEntriesWithNoEndingTime(), $number);
    }

    /**
     * @Then /^I should have no entries with no ending time$/
     */
    public function iShouldHaveNoEntriesWithNoEndingTime(): void
    {
        $this->iShouldHaveEntriesWithNoEndingTime(0);
    }

    /**
     * @Then /^I should have a project (.*)$/
     */
    public function iShouldHaveAProjectPROJ(string $project): void
    {
        Assert::true($this->projRepo->exists($project));
    }

    /**
     * @Given /^I should have (?:only )?(\d+) projects?$/
     */
    public function iShouldHaveProject($count): void
    {
        Assert::eq($count, $this->projRepo->count([]));
    }

    /**
     * @When /^I request the stats(?: for (.*))?$/
     */
    public function iRequestTheStats(string $date = null): void
    {
        $period = new Period();
        if ($date) {
            $period = new Period(
                new DateTimeImmutable($date),
                new DateTimeImmutable($date)
            );
        }
        $service = new StatisticsService($this->em);
        $this->stats = $service->getProjectStats($period);
    }

    /**
     * @Then /^I should have (\d+) hours? in (.*)/
     */
    public function iShouldHaveHoursInProject($hours, $project): void
    {
        Assert::eq($hours, $this->stats->getProjectHours($project));
    }

    /**
     * @Given /^the total should be (\d+) hours?$/
     */
    public function theTotalShouldBeHours($hours): void
    {
        Assert::eq($hours, $this->stats->getTotal());
    }
}
