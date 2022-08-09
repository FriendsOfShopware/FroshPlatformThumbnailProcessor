#!/usr/bin/env bash
echo "Fix php files"
php ../../../dev-ops/analyze/vendor/bin/php-cs-fixer fix --config=../../../vendor/shopware/platform/.php_cs.dist -vv .
