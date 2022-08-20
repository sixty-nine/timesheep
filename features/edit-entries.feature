@functional
Feature: Using entry:edit

    As a user
    I want to be able to edit entries from my timesheet
    In order to fix my errors

    Background:
        Given my timesheet is empty

    Scenario: Edit entry
        Given I have an entry from 10:00 to 15:00
         When I call the "entry:edit" command with {"--date": "10:00", "start": "12:00", "end": "13:00", "-f": "true"}
          And I should have 1 entries
          And I should have an entry from 12:00 to 13:00

    Scenario: Delete an not existing entry should not be possible
         When I call the "entry:edit" command with {"--date": "10:00", "--force": "true"}
         Then the command should fail
