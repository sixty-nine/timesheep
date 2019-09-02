<?php

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use SixtyNine\Timesheep\Bootstrap;
use SixtyNine\Timesheep\Helper\Doctrine as DoctrineHelper;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Webmozart\Assert\Assert;

class DbFeatureContext implements Context
{
    use CastingDateTrait;
    use CastingTimeToDateTrait;
    use CastingTimesToPeriodTrait;
    use RunningCommandsTrait;

    /** @var EntityManager */
    private $em;
    /** @var EntryRepository $entryRepo */
    private $entryRepo;
    /** @var ContainerInterface */
    private $container;

    public function __construct()
    {
        $this->container = Bootstrap::boostrap();
        $this->em = $this->container->get('em');
        $this->entryRepo = $this->em->getRepository(Entry::class);
    }

    /**
     * @Given /^I have an empty database$/
     * @Given /^I my timesheet is empty$/
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
     * @Then /^I should have an?(?: new)? entry (.*)$/
     */
    public function iShouldHaveANewEntryFromTo(Period $period)
    {
        $entry = $this->entryRepo->findEntry($period);
        Assert::notNull($entry, 'Entry not found');
    }

    /**
     * @Given /^I should have (\d+) entr(?:y|ies)$/
     */
    public function iShouldHaveEntries(int $number)
    {
        Assert::count($this->entryRepo->findAll(), $number);
    }
}
