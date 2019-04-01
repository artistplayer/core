#!/usr/bin/env bash

sudo apt-get install omxplayer python-dbus -y

# Install OMX Service
sudo chmod 0777 /home/signalize/core/services/omx.service
sudo systemctl enable /home/signalize/core/services/omx.service