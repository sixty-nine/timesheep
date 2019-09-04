@functional
Feature: Prevent wrong entries

    As a user
    I want to be make sure I cannot create wrong entries
    So that my timesheet remains consistent

    Scenario: Create entry
        Given my timesheet is empty
         When I call the "add" command with:
            | start   | 10:00  |
            | end     | 11:00  |
            | -f | true   |
         Then the command should succeed
          And I should have an entry from 10:00 to 11:00
          And I should have 1 entry

    Scenario: No already existing new entry
         When I call the "add" command with {"start": "10:00", "end": "11:00", "-f": "true"}
         Then the command should fail

    Scenario: No overlapping entries
         When I call the "add" command with {"start": "10:00", "end": "12:00", "-f": "true"}
         Then the command should fail
         When I call the "add" command with {"start": "9:00", "end": "11:00", "-f": "true"}
         Then the command should fail
         When I call the "add" command with {"start": "9:00", "end": "10:30", "-f": "true"}
         Then the command should fail
         When I call the "add" command with {"start": "10:30", "end": "12:30", "-f": "true"}
         Then the command should fail
          And I should have 1 entry

    Scenario: No entries encompassed by an existing one
         When I call the "add" command with {"start": "10:15", "end": "10:45", "-f": "true"}
         Then the command should fail
          And I should have 1 entry

    Scenario: No entries encompassing an existing one
         When I call the "add" command with {"start": "9:00", "end": "14:00", "-f": "true"}
         Then the command should fail
          And I should have 1 entry
