#!/usr/bin/env bash

sudo apt-get install python-dbus rng-tools hostapd -y

# Install OMX Service
sudo chmod 0777 /home/signalize/core/services/bluetooth.service
sudo systemctl enable /home/signalize/core/services/bluetooth.service