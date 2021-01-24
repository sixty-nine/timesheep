# Listing entries

## Selecting the period

The period of time to display can be selected with the two parameters:

 * `--from` is start time
 * `--to` is the end time

The values for these parameters can be any PHP-parsable date.

Examples:

```
--from 1.1.2021
--from "last week"
--from "2 days ago"
-- to "last monday"
```

## Selecting the time span

Timesheep can automatically select defined time spans:

 * `--day` - only one day
 * `--week` - only one week
 * `--month` - only one month

This can combined with the `--from` switch for powerful selectors:

```
# Only today
--day

# Only last Friday
--from "last friday" --day

# The whole last week
--from "last monday" --week

# This month
--month

# Last month
--from "last month" --month
```

## Display switches

The information shown in the results changes depending on the display switches:

 * no switch will show the raw entries
 * `--stats` will show entries statistics
 * `--presence` will show presence time

#### Listing entries

Replies to the question: what did I do that day?

`bin/ts e:ls  --from "last friday"`

```
┌────────────┬───────┬───────┬──────────┬───────────┬──────┬─────────────┐
│ Day        │ From  │ To    │ Duration │ Project   │ Task │ Description │
├────────────┼───────┼───────┼──────────┼───────────┼──────┼─────────────┤
│ 2021-01-22 │ 06:00 │ 06:30 │    00:30 │ mn8       │      │             │
│            │ 06:30 │ 10:30 │    04:00 │ mn-secure │      │             │
│            │ 10:30 │ 10:45 │    00:15 │ mn-secure │      │             │
│            │ 11:30 │ 11:45 │    00:15 │ daily     │      │             │
│            │ 13:00 │ 13:45 │    00:45 │ inluft    │      │             │
│            │ 19:00 │ 19:30 │    00:30 │ mn-secure │      │             │
└────────────┴───────┴───────┴──────────┴───────────┴──────┴─────────────┘
```

#### Entries statistics

Replies to the question: how much time did I spend on each project?

`bin/ts e:ls  --from "last friday" --stats`

```
From: 2021-01-22
To: -

┌───────────┬──────────┬──────┐
│ Project   │ Duration │      │
├───────────┼──────────┼──────┤
│ daily     │ 0.25h    │ 0:15 │
│ inluft    │ 0.75h    │ 0:45 │
│ mn-secure │ 4.75h    │ 4:45 │
│ mn8       │ 0.5h     │ 0:30 │
└───────────┴──────────┴──────┘

Total: 6:15h
Decimal: 6.25h
```

#### Presence time

Replies to the question: when was I working?

`bin/ts e:ls  --from "last friday" --presence`

```
Entries
=======

From: 2021-01-22
To: -

┌────────────┬───────┬───────┬──────────┬────────┐
│ Date       │ Start │ End   │ Duration │        │
├────────────┼───────┼───────┼──────────┼────────┤
│ 2021-01-22 │ 06:00 │ 10:45 │ 04:45    │ 4.75 h │
│ 2021-01-22 │ 11:30 │ 11:45 │ 00:15    │ 0.25 h │
│ 2021-01-22 │ 13:00 │ 13:45 │ 00:45    │ 0.75 h │
│ 2021-01-22 │ 19:00 │ 19:30 │ 00:30    │ 0.5 h  │
└────────────┴───────┴───────┴──────────┴────────┘

Total: 6:15h
Decimal: 6.25h
```
