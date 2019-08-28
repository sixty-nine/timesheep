#!/usr/bin/env bash

#set -x

echo "--- RUNNING PHPSTAN ---------------------------"
composer run check:stan

if [ $? -ne 0 ]; then
    exit 1
fi

echo "--- RUNNING LINT ---------------------------"
composer run check:lint

if [ $? -ne 0 ]; then
    exit 1
fi

echo
echo "--- RUNNING TESTS ---------------------------"
composer run test
echo

echo
echo "--- RUNNING BEHAT ---------------------------"
vendor/bin/behat --colors
echo

