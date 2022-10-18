#!/usr/bin/env bash

plugin_base_dir="$( cd "$( dirname "$0" )/.." >/dev/null 2>&1 && pwd )"

cd "${plugin_base_dir}" && composer dump-autoload && >/dev/null cd -

[ -x "$(command -v docker)" ] && [ "$(docker info &> /dev/null; echo $?)" -eq "0" ] \
    && docker run --rm -v "${plugin_base_dir}:/app" aragon999/phpstan-shopware:v6.2.2 analyze src \
    || echo "Docker not installed or not running, skipping phpstan check"

[ ! -f ../../../dev-ops/analyze/vendor/bin/psalm  ] \
    && echo "Cannot find psalm executable, skipping psaml check" \
    || php ../../../dev-ops/analyze/vendor/bin/psalm --config=psalm.xml --show-info=false
