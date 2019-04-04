#!/usr/bin/env bash

SECUREKEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
PASSWORD=$(echo ${SECUREKEY}|cut -c 1-8)

echo "Encrypt the Raspberry Box..."
echo -e "raspberry\n${PASSWORD}\n${PASSWORD}" | sudo passwd pi