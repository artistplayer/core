#!/usr/bin/env bash

sudo apt-get install omxplayer python-dbus -y

# Install OMX Service
sudo ln -s /home/signalize/core/services/omx.service /etc/systemd/system/core/signalize-omx.service
sudo systemctl start signalize-omx
sudo systemctl enable signalize-omx