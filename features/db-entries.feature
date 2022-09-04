@functional
Feature: Creation of db entries

    As a developer
    I need to make sure an entry overlapping an existing one cannot be created
    In order to maintain the timesheet consistency

    Background:
        Given I have an empty database

    Scenario:
        Given I have an entry from 10:00 to 15:00
         Then I should have an entry from 10:00 to 15:00
          And I should have 1 entry
          And I should not be able to create an entry from 09:00 to 18:00
          And I should not be able to create an entry from 11:00 to 12:00
          And I should not be able to create an entry from 9:00 to 12:00
          And I should be able to create an entry from 09:00 to 10:00

         Then I should have a new entry from 09:00 to 15:00
          And I should have 1 entries
          And I should not be able to create an entry from 7:00 to 10:00
          And I should be able to create an entry from 7:00 to 9:00
          And I should have 1 entries

