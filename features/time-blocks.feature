@unit
Feature: Test NonOverlappingPeriodList class

    As a developer
    I want to make sure NonOverlappingPeriodList properly stores non contiguous block
    So that it can be used to calculate presences

    Scenario: Non-contiguous periods
        Given I have a time block list
        When I add the period from 10:00 to 11:00
        And I add the period from 12:00 to 13:00
        And I add the period from 14:00 to 15:00
        Then the time block must contain 3 entries

    Scenario: Contiguous periods
        Given I have a time block list
        When I add the period from 10:00 to 11:00
        And I add the period from 11:00 to 12:00
        And I add the period from 12:00 to 13:00
        Then the time block must contain 1 entry

    Scenario: Touching before
        Given I have a time block list
        When I add the period from 10:00 to 11:00
        And I add the period from 11:00 to 12:00
        And I add the period from 13:00 to 14:00
        Then the time block must contain 2 entries

    Scenario: Touching after
        Given I have a time block list
        When I add the period from 10:00 to 11:00
        And I add the period from 12:00 to 13:00
        And I add the period from 13:00 to 14:00
        Then the time block must contain 2 entries
