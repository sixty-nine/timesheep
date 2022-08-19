# TimeSheep 🐑

[![Run checks](https://github.com/sixty-nine/timesheep/actions/workflows/behat.yml/badge.svg)](https://github.com/sixty-nine/timesheep/actions)

Simple timesheet manager.

## Install

```bash
bin/install
```

## Usage

See also [Listing entries](doc/entries-list.md).

### Creating entries

```bash
# Add an entry interactively
bin/ts add
# Add an entry today from 14:00 to 16:00
bin/ts add 14:00 16:00
# Add an entry without confirmation
bin/ts e:add 5:00 6:15 -f
# Create an entry for tomorrow
bin/ts add "tomorrow 10:00" "tomorrow 11:00"
# Create an entry for yesterday
bin/ts add "yesterday 10:00" "yesterday 11:00"
# Entry spawning over multiple days
bin/ts add "2019-01-01 23:00" "2019-01-02 01:00"
# Entry 2 days ago
bin/ts add "2 days ago 08:00" "2 days ago 09:30"
```

### Listing entries

```bash
# List all entries
bin/ts ls
# List entries from today on
bin/ts ls --from today
# List entries from yesterday to today
bin/ts ls --from yesterday --to today
# List all entries from last week
bin/ts ls --from "last week" --week
# List all entries of this month
bin/ts ls --month
```

## PHAR

Timesheep can be run as a standalone app.

`bin/ts create-phar` will create:

 * `dist/ts` - the Timesheep executable
 * `dist/database.db` - an empty database

You must copy the database file along with the ts executable.
