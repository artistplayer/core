#!/usr/bin/env bash

# Update package manager
sudo apt-get install ca-certificates apt-transport-https -y
sudo wget -q https://packages.sury.org/php/apt.gpg -O- | sudo apt-key add -
sudo echo "deb https://packages.sury.org/php/ buster main" | sudo tee /etc/apt/sources.list.d/php7.list
sudo apt-get update -y

# Install PHP and Required Libraries
sudo apt-get install php7.3-fpm php7.3-opcache php7.3-curl php7.3-mbstring php7.3-pgsql php7.3-zip php7.3-xml php7.3-gd php7.3-sqlite3 -y
sudo sed -i 's/memory_limit = -1/memory_limit = 512MB/g'  /etc/php/7.3/cli/php.ini
