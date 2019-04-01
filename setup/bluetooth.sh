#!/usr/bin/env bash

sudo apt-get install python-dbus rng-tools hostapd -y

# Install OMX Service
sudo chmod 0777 /home/signalize/core/services/bluetooth.service
sudo ln -s /home/signalize/core/services/bluetooth.service /etc/systemd/system/signalize-bluetooth.service
sudo systemctl start signalize-bluetooth
sudo systemctl enable signalize-bluetooth