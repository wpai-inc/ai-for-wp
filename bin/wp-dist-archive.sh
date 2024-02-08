#!/bin/bash

# remove the cwp-helper-plugin zip file if it exists
rm ../ai-for-wp.*.zip

# remove the vendor directory
rm -rf vendor

# run composer in production mode
composer install --no-dev --prefer-dist --optimize-autoloader

npm run build

npm run make-pot

# create the dist archive
echo "Creating dist archive..."
wp dist-archive .

# remove the vendor directory
rm -rf vendor

# run composer in development mode
composer install --prefer-dist