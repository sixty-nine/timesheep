Feature: Check-in, check-out, and due time calculation

    As a user
    I need to be able to log my worked time
    In order count how long I must still work this week

    Background:
        Given I am a timesheep user
        Given my weekly due time is 10h
        Given I have a project named "test"
        Given my timesheet is empty
        Given today is 26-06-1969

    Scenario: Check-in and check-out
        When I check-in to the project "test" at 10:00
        Then I should have a current entry in my timesheet
         And the current entry date should be 26-06-1969
         And the current entry start time should be 10:00
         And the current entry should be in project "test"
         And the current entry should not have an end time
         And my weekly due time should be 10h

        When I check-out from the project "test" at 11:00
        Then the current entry end time should be 11:00
         And the current entry duration should be 1h
         And my weekly due time should be 9h

        When I check-in to the project "test" at 12:00
         And I check-out from the project "test" at 18:00
        Then the current entry start time should be 12:00
         And the current entry end time should be 18:00
         And the current entry duration should be 6h
         And my weekly due time should be 3h

        When I add an entry to project "test" from 20:00 to 23:00
        Then the current entry start time should be 20:00
         And the current entry end time should be 23:00
         And the current entry duration should be 3h
         And my weekly due time should be 0h

        When I add an entry to project "test" from 23:00 to 00:00
        Then the current entry start time should be 23:00
         And the current entry end time should be tomorrow at 00:00
         And the current entry duration should be 1h
         And my weekly due time is -1h
