#!/usr/bin/env bash

sudo sh -c "curl -sS https://getcomposer.org/installer | php"
sudo mv composer.phar /usr/bin/composer

echo "Verify Composer Install"
echo "Installed Version:"
composer --version