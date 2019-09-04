<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use SixtyNine\Timesheep\Bootstrap;
use SixtyNine\Timesheep\Domain\Model\Entry;
use SixtyNine\Timesheep\Domain\Model\Project;
use SixtyNine\Timesheep\Domain\Model\Timesheet;
use SixtyNine\Timesheep\Domain\Model\User;
use Webmozart\Assert\Assert;

use function DeepCopy\deep_copy;

/**
 * Defines application features from the specific context.
 */
class old implements Context
{
    use CastingHoursToIntTrait;
    use CastingDateTrait;
    use CastingTimeToDateTrait;

    /** @var User */
    private $user;
    /** @var Project[] */
    private $projects = [];
    /** @var Timesheet */
    private $timesheet;
    /** @var Timesheet */
    private $prevTs;

    /**
     * FeatureContext constructor.
     */
    public function __construct()
    {
        Bootstrap::boostrap();
    }

    /** @BeforeStep */
    public function prepare(BeforeStepScope $scope): void
    {
        if ('the current entry should be unchanged' !== $scope->getStep()->getText()) {
            $this->prevTs = deep_copy($this->timesheet);
        }
    }

    /**
     * @Transform /^"([^"]+)" project$/
     * @Transform /^project "([^"]+)"$/
     * @Transform :customer
     */
    public function castNameToProject(string $name): ?Project
    {
        if (!array_key_exists($name, $this->projects)) {
            throw new \Exception('Project not found: '.$name);
        }
        return $this->projects[$name];
    }

    /**
     * @Transform /^(\d?\d:\d\d)$/
     */
    public function castTimeToCurDate(string $time): DateTimeImmutable
    {
        return $this->castTimeToDate($time, $this->timesheet->getCurDate());
    }

    /**
     * @Given I am a timesheep user
     */
    public function iAmATimesheepUser(): void
    {
        $this->user = new User('timesheep');
    }

    /**
     * @Given /my weekly due time is (-?\d+h)/
     */
    public function myWeeklyDueTimeIs($hours): void
    {
        $this->user->setWeeklyDueHours((int)$hours);
    }

    /**
     * @Given /I have a project named "([^"]*)"/
     * @param string $name
     */
    public function iHaveAProjectNamed(string $name): void
    {
        if (!array_key_exists($name, $this->projects)) {
            $this->projects[$name] = new Project($name);
        }
    }

    /**
     * @Given my timesheet is empty
     */
    public function myTimesheetIsEmpty(): void
    {
        $this->timesheet = new Timesheet();
    }

    /**
     * @Given /^today is (.*)$/
     */
    public function todayIs(DateTimeImmutable $date)
    {
        $this->timesheet->setCurDate($date);
    }

    /**
     * @When /I check-in to the (project "[^"]+") at (.*)/
     */
    public function iCheckInToTheProjectAt2(Project $project, DateTimeImmutable $startTime): void
    {
        $this->timesheet->checkInAt($project, $startTime);
    }

    /**
     * @Then I should have a current entry in my timesheet
     */
    public function iShouldHaveACurrentEntry(): void
    {
        Assert::notNull($this->timesheet->getCurEntry(), 'There is no current entry');
    }

    /**
     * @Then /the current entry start time should be (.*)/
     */
    public function theCurrentEntryStartTimeShouldBe(DateTimeImmutable $time): void
    {
        $this->iShouldHaveACurrentEntry();
        $this->assertDateEq($this->timesheet->getCurEntry()->getStart(), $time);
    }
    /**
     * @Then /the current entry should be in (project "[^"]+")/
     */
    public function theCurrentEntryShouldBeInProject(Project $project): void
    {
        Assert::eq($this->timesheet->getCurEntry()->getProject(), $project);
    }

    /**
     * @Then the current entry should not have an end time
     */
    public function theCurrentEntryShouldNotHaveAnEndTime(): void
    {
        Assert::null($this->timesheet->getCurEntry()->getEnd());
    }

    /**
     * @Then /my weekly due time should be (\d+h)/
     */
    public function myWeeklyDueTimeShouldBe(int $hours): void
    {
        $due = $this->user->getWeeklyDueHours() - $this->timesheet->getWorkedTime();
        Assert::eq($hours, $due);
    }

    /**
     * @When /I check-out from the (project "[^"]+") at (.*)/
     */
    public function iCheckoutFromTheProjectAt(Project $project, DateTimeImmutable $endTime): void
    {
        $this->iShouldHaveACurrentEntry();
        Assert::eq($project, $this->timesheet->getCurEntry()->getProject());
        $this->timesheet->getCurEntry()->setEnd($endTime);
    }

    /**
     * @Then the current entry should be unchanged
     */
    public function theCurrentEntryShouldBeUnchanged(): void
    {
        Assert::eq($this->prevTs, $this->timesheet);
    }

    /**
     * @Then /the current entry end time should be (tomorrow at )?(.*)/
     */
    public function theCurrentEntryEndTimeShouldBe($tomorrow, DateTimeImmutable $time): void
    {
        $this->iShouldHaveACurrentEntry();
        if ($tomorrow) {
            $time = $time->modify('+1 day');
        }
        $this->assertDateEq($this->timesheet->getCurEntry()->getEnd(), $time);
    }

    /**
     * @Then /the current entry duration should be (-?\d+h)/
     */
    public function theCurrentEntryDurationShouldBe(int $hours): void
    {
        $this->iShouldHaveACurrentEntry();
        Assert::eq($hours, $this->timesheet->getCurEntry()->getDuration());
    }

    /**
     * @When /I add an entry to (project "[^"]+") from (.*) to (.*)/
     */
    public function iAddAnEntryToProjectFromTo(
        Project $project,
        DateTimeImmutable $startTime,
        DateTimeImmutable $endTime
    ): void {
        while ($endTime < $startTime) {
            $endTime = $endTime->modify('+1 day');
        }
        $this->timesheet->addEntry(
            new Entry($project, $startTime, $endTime)
        );
    }

    protected function assertDateEq(DateTimeImmutable $date1, DateTimeImmutable $date2, string $format = null)
    {
        if ($format) {
            Assert::eq(
                $date1->format($format),
                $date2->format($format),
                sprintf('Expected %s got %s', $date2->format('r'), $date1->format('r'))
            );
        } else {
            Assert::eq(
                $date1,
                $date2,
                sprintf('Expected %s got %s', $date2->format('r'), $date1->format('r'))
            );
        }
    }

    /**
     * @Given /^the current entry date should be (.*)$/
     */
    public function theCurrentEntryDateShouldBe(DateTimeImmutable $date)
    {
        $this->assertDateEq($date, $this->timesheet->getCurEntry()->getStart(), 'Y-m-d');
    }

}
