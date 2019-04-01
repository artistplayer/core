#!/usr/bin/env bash

if !(id "signalize" >/dev/null 2>&1); then
    echo "* Create user"
    SECUREKEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
    PASSWORD=$(echo ${SECUREKEY}|cut -c 1-8)
    sudo useradd signalize -p $(openssl passwd -crypt ${PASSWORD}) -m -d /home/signalize -G sudo,dialout,audio,video,bluetooth
fi