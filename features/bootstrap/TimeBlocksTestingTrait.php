<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Model\TimeBlocks;
use Webmozart\Assert\Assert;

trait TimeBlocksTestingTrait
{
    /** @var TimeBlocks */
    private $timeBlocks = null;

    /**
     * @Given /^I have a time block list$/
     */
    public function iHaveATimeBlockList(): void
    {
        $this->timeBlocks = new TimeBlocks();
    }

    /**
     * @When /^I add the period (.*)$/
     */
    public function iAddThePeriod(Period $p): void
    {
        Assert::notNull($this->timeBlocks, 'No current time block');
        $this->timeBlocks->addPeriod($p);
    }

    /**
     * @Then /^the time block must contain (\d+) entr(?:y|ies)$/
     */
    public function theTimeBlockMustContainEntries(int $count): void
    {
        Assert::notNull($this->timeBlocks, 'No current time block');
        Assert::count($this->timeBlocks->getPeriods(), $count);
    }
}
