#!/usr/bin/env bash

export PHP_CS_FIXER=~/.composer/vendor/bin/php-cs-fixer

if ! [ -x "$(command -v ${PHP_CS_FIXER})" ]; then
    echo 'Command `php-cs-fixer` not found. Installing...'
    composer global require friendsofphp/php-cs-fixer
fi

${PHP_CS_FIXER} fix $@ .
