#!/usr/bin/env bash

SCRIPTPATH="$( cd "$(dirname "$0")" ; pwd -P )"
VENDOR_BIN=$(realpath $SCRIPTPATH/../vendor/bin)
ARTEFACTS=$(realpath $SCRIPTPATH/../artefacts)
COMPOSER=composer
BROWSER=/usr/bin/google-chrome

$VENDOR_BIN/phpunit  -c tests --colors=always \
    --coverage-html $ARTEFACTS/coverage/html \
    --coverage-xml $ARTEFACTS/coverage/xml  \
    && \
    $BROWSER $ARTEFACTS/coverage/html/index.html > /dev/null 2>&1
