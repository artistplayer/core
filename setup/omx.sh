#!/usr/bin/env bash

sudo apt-get install mplayer -y

# Install MPlayer Service
sudo chmod 0777 /home/signalize/core/services/signalize-mplayer.service
sudo systemctl enable /home/signalize/core/services/signalize-mplayer.service
