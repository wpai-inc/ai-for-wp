#!/bin/bash

# remove the cwp-helper-plugin zip file if it exists
rm ../cwp-helper-plugin.*.zip

# remove the vendor directory
rm -rf vendor

# run composer in production mode
composer install --no-dev --prefer-dist --optimize-autoloader

# create the dist archive
echo "Creating dist archive..."
wp dist-archive .

# remove the vendor directory
rm -rf vendor

# run composer in development mode
composer install --prefer-dist