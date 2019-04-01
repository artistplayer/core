#!/usr/bin/env bash

# Create User
wget -O -  https://github.com/artistplayer/core/raw/master/setup/user.sh --no-cache -q | bash

# Install NGINX Server
wget -O -  https://github.com/artistplayer/core/raw/master/setup/server.sh --no-cache -q | bash

# Install Composer
wget -O -  https://github.com/artistplayer/core/raw/master/setup/composer.sh --no-cache -q | bash

# Install PHP
wget -O -  https://github.com/artistplayer/core/raw/master/setup/php.sh --no-cache -q | bash

# Install USB Mounting Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/usb.sh --no-cache -q | bash

# Install project
wget -O -  https://github.com/artistplayer/core/raw/master/setup/project.sh --no-cache -q | bash

# Configure OMX Media player Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/omx.sh --no-cache -q | bash

# Configure Bluetooth Tether Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/bluetooth.sh --no-cache -q | bash

# Configure Device as Hotspot
#wget -O -  https://github.com/artistplayer/core/raw/master/setup/hotspot.sh --no-cache -q | bash

# Install Raspotify
curl -sL https://dtcooper.github.io/raspotify/install.sh --no-cache -q | sh

# Reboot device
reboot
