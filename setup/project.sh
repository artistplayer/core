#!/usr/bin/env bash

# Create database, if not exists
if [ ! -f /home/signalize/database.sqlite ]; then
    sudo su - signalize -c "touch /home/signalize/database.sqlite"
    sudo chmod 0777 /home/signalize/database.sqlite
fi

# Unpack project
sudo apt-get install git -y

if [ ! -f /home/signalize/core/composer.lock ]; then
    sudo su - signalize -c "git clone https://github.com/artistplayer/core.git core"
    sudo su - signalize -c "cd core && composer install"
else
    sudo su - signalize -c "composer clearcache"
    sudo su - signalize -c "cd core && git pull"
    sudo su - signalize -c "cd core && composer update"
fi

# Config project
sudo su - signalize -c "cd core && cp .env.example .env"


sudo chmod 0777 core/storage -Rf
sudo su - signalize -c "cd core && php artisan migrate"
sudo su - signalize -c "cd core && php artisan storage:link"
sudo su - signalize -c "cd core && php artisan optimize"

# Change NGINX config
sudo rm /etc/nginx/sites-enabled/default
sudo ln -s /home/signalize/core/signalize.conf /etc/nginx/sites-enabled/default
sudo systemctl reload nginx

# Setup Socket Service
sudo ln -s /home/signalize/core/services/socket.service /etc/systemd/system/signalize-socket.service
sudo systemctl start signalize-socket
sudo systemctl enable signalize-socket