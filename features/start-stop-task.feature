@functional
Feature: Using e:start and e:stop

    As a user
    I want to be able to add tasks by start and stopping them
    So that I don't have to know their duration in advance

    Scenario: Start a task
        Given my timesheet is empty
        When I call the "start" command with {"--force": "true"}
        Then the command should succeed
        # There can be a race-condition here if the command is called when
        # the date is rounded down and we check after the date is rounded up.
        And I should have an entry starting now
        And I should have 1 entry with no ending time

    Scenario: A task cannot be started if there is already a started task
        Given my timesheet is empty
        And I call the "start" command with {"--force": "true"}
        And the command should succeed
        And I should have 1 entry with no ending time
        When I call the "start" command with {"--force": "true"}
        Then the command should fail
        And I should have 1 entry with no ending time

    Scenario: I cannot end a task if there is no started task
        Given my timesheet is empty
        And I call the "start" command with {"--force": "true"}
        And I should have 1 entry with no ending time
        When I call the "stop" command
        Then the command should succeed
        And I should have no entries with no ending time

    Scenario: I cannot end a task if there is no started task
        Given my timesheet is empty
        When I call the "stop" command
        Then the command should fail
        And I should have no entries with no ending time
