<?php

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Bootstrap;
use SixtyNine\Timesheep\Helper\Doctrine as DoctrineHelper;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Webmozart\Assert\Assert;

class DbFeatureContext implements Context
{
    use CastingDateTrait;
    use CastingTimeToDateTrait;
    use CastingTimesToSpanTrait;

    /** @var EntityManager */
    private $em;
    /** @var DateTimeImmutable */
    private $curDate;
    /** @var EntryRepository $entryRepo */
    private $entryRepo;

    public function __construct()
    {
        $container = Bootstrap::boostrap();
        $this->em = $container->get('em');
        $this->entryRepo = $this->em->getRepository(Entry::class);
    }

    /**
     * @Given /^I have an empty database$/
     */
    public function iHaveAnEmptyDatabase()
    {
        DoctrineHelper::truncateAll($this->em->getConnection());
    }

    /**
     * @Given /^I have an entry (.*)$/
     * @Given /^I should be able to create an entry (.*)$/
     */
    public function iHaveAnEntryFromTo(array $startEnd)
    {
        [$start, $end] = $startEnd;
        $entry = $this->entryRepo->create($start, $end);
    }

    /**
     * @Then /^I should not be able to create an entry (.*)$/
     */
    public function iShouldNotBeAbleToCreateAnEntryFromTo(array $startEnd)
    {
        [$start, $end] = $startEnd;
        try {
            $thrown = false;
            $this->entryRepo->create($start, $end);
        } catch (\Exception $ex) {
            $thrown = true;
        }

        Assert::true($thrown, 'The entry could be created');
    }

    /**
     * @Then /^I should have an?(?: new)? entry (.*)$/
     */
    public function iShouldHaveANewEntryFromTo(array $startEnd)
    {
        [$start, $end] = $startEnd;
        $entry = $this->entryRepo->findEntry($start, $end);
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
