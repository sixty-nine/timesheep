@functional @current
Feature: Project statistics

    As a user
    I want to have project statistics
    So that I can see the time spent on each project

    Background:
        Given my timesheet is empty

    Scenario:
        When I call the "add" command with {"start": "8:00", "end": "10:00", "project": "PROJ1", "-f": "true"}
        Then I should have a project PROJ1
         And I should have only 1 project
         And I should have an new entry from 8:00 to 10:00 in project PROJ1

        When I call the "add" command with {"start": "10:00", "end": "11:00", "project": "PROJ2", "-f": "true"}
        Then I should have a project PROJ2
         And I should have 2 projects
         And I should have an new entry from 10:00 to 11:00 in project PROJ2

        When I call the "add" command with {"start": "11:00", "end": "12:00", "project": "PROJ1", "-f": "true"}
         And I should have only 2 projects
         And I should have an new entry from 11:00 to 12:00 in project PROJ1

        When I call the "add" command with {"start": "tomorrow 14:00", "end": "tomorrow 18:00", "project": "PROJ3", "-f": "true"}
        Then I should have a project PROJ3
         And I should have 3 projects
         And I should have an new entry from 14:00 to 18:00 on tomorrow in project PROJ3

        When I request the stats
        Then I should have 3 hours in PROJ1
         And I should have 1 hour in PROJ2
         And I should have 4 hour in PROJ3
         And the total should be 8 hours

        When I request the stats for today
        Then I should have 3 hours in PROJ1
         And I should have 1 hour in PROJ2
         And the total should be 4 hours

        When I request the stats for tomorrow
        Then I should have 4 hours in PROJ3
         And the total should be 4 hours

        When I request the stats for yesterday
        Then the total should be 0 hours
