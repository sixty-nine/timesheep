<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

use SixtyNine\Timesheep\Model\NonOverlappingPeriodList;
use SixtyNine\Timesheep\Model\Period;
use Webmozart\Assert\Assert;

trait TimeBlocksTestingTrait
{
    /** @var NonOverlappingPeriodList */
    private $periodList = null;

    /**
     * @Given /^I have a time block list$/
     */
    public function iHaveATimeBlockList(): void
    {
        $this->periodList = new NonOverlappingPeriodList();
    }

    /**
     * @When /^I add the period (.*)$/
     */
    public function iAddThePeriod(Period $p): void
    {
        Assert::notNull($this->periodList, 'No current time block');
        $this->periodList->addPeriod($p);
    }

    /**
     * @Then /^the time block must contain (\d+) entr(?:y|ies)$/
     */
    public function theTimeBlockMustContainEntries(int $count): void
    {
        Assert::notNull($this->periodList, 'No current time block');
        Assert::count($this->periodList->getPeriods(), $count);
    }
}
