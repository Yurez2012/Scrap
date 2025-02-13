#!/bin/bash

# Check if this is the first run
if [ ! -e /var/www/.firstrun ]; then
    # Put your commands here
    cd /var/www/

    # only execute next command if the direcotory vendor does not exist
    [ ! -d "vendor" ] && composer install --no-interaction --optimize-autoloader

    php artisan migrate

    php artisan app:parse-csv-file-with-whiskey-command

    npm install

    npm run build

    php artisan key:generate

    # Set the flag
    touch /var/www/.firstrun
fi

php-fpm
