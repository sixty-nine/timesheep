@functional
Feature: Using entry:delete

    As a user
    I want to be able to delete entries from my timesheet
    In order to fix my errors

    Background:
        Given my timesheet is empty

    Scenario: Delete an existing entry should be possible
        Given I have an entry from 10:00 to 15:00
          And I should have 1 entry

         When I call the "entry:delete" command with {"--date": "10:00", "--force": "true"}
          And I should have 0 entries
          And I should be able to create an entry from 9:00 to 12:00

    Scenario: Delete an not existing entry should not be possible
         When I call the "entry:delete" command with {"--date": "10:00", "--force": "true"}
         Then the command should fail
