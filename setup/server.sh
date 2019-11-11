#!/usr/bin/env bash
sudo apt-get update -y
sudo apt-get install nginx -y

sudo sed -i 's/user www-data/user signalize/g' /etc/nginx/nginx.conf
