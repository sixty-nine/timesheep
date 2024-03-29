@functional
Feature: Using entry:add

    As a user
    I want to be able to add entries to my timesheet
    In order to track the time I spend on projects

    Scenario: Create entry for today
        Given my timesheet is empty
         When I call the "add" command with {"start": "10:00", "end": "11:00", "--force": "true"}
         Then the command should succeed
          And I should have an entry from 10:00 to 11:00

         When I call the "add" command with {"start": "9:00", "end": "10:00", "--force": "true", "project": "p1"}
         Then the command should succeed
         And I should have an entry from 9:00 to 10:00

         When I call the "add" command with {"start": "11:00", "end": "12:00", "--force": "true", "project": "p2"}
         Then the command should succeed
          And I should have an entry from 11:00 to 12:00
          And I should have 3 entry

    Scenario: Tomorrow entries do not overlap today's one
         When I call the "add" command with {"start": "tomorrow 10:00", "end": "tomorrow 11:00", "--force": "true"}
         Then the command should succeed
          And I should have 4 entry

    Scenario: Start time before end time gets shifted to tomorrow
        When I call the "add" command with {"start": "23:00", "end": "00:00", "-f": "true"}
        Then the command should succeed
         And I should have an entry from 23:00 to 00:00
         And I should have 5 entry

    Scenario: Similar entry before gets merged
        Given my timesheet is empty
          And I call the "add" command with {"start": "10:00", "end": "11:00", "--force": "true"}
         When I call the "add" command with {"start": "11:00", "end": "12:00", "--force": "true"}
          And I should have an entry from 10:00 to 12:00
          And I should have 1 entry

    Scenario: Similar entry after gets merged
        Given my timesheet is empty
          And I call the "add" command with {"start": "11:00", "end": "12:00", "--force": "true"}
         When I call the "add" command with {"start": "10:00", "end": "11:00", "--force": "true"}
          And I should have an entry from 10:00 to 12:00
          And I should have 1 entry

    Scenario: Similar entries before and after get merged
        Given my timesheet is empty
          And I call the "add" command with {"start": "10:00", "end": "11:00", "--force": "true"}
          And I call the "add" command with {"start": "12:00", "end": "13:00", "--force": "true"}
         When I call the "add" command with {"start": "11:00", "end": "12:00", "--force": "true"}
          And I should have an entry from 10:00 to 13:00
          And I should have 1 entry
