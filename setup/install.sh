#!/usr/bin/env bash

# Create User
wget -O -  https://github.com/artistplayer/core/raw/master/setup/user.sh --no-cache | bash

# Install NGINX Server
wget -O -  https://github.com/artistplayer/core/raw/master/setup/server.sh --no-cache | bash

# Install Composer
wget -O -  https://github.com/artistplayer/core/raw/master/setup/composer.sh --no-cache | bash

# Install PHP
wget -O -  https://github.com/artistplayer/core/raw/master/setup/php.sh --no-cache | bash

# Install USB Mounting Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/usb.sh --no-cache | bash

# Install project
wget -O -  https://github.com/artistplayer/core/raw/master/setup/project.sh --no-cache | bash

# Configure OMX Media player Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/omx.sh --no-cache | bash

# Configure Bluetooth Tether Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/bluetooth.sh --no-cache | bash

# Configure Device as Hotspot
#wget -O -  https://github.com/artistplayer/core/raw/master/setup/hotspot.sh --no-cache | bash

# Install Raspotify
curl -sL https://dtcooper.github.io/raspotify/install.sh --no-cache | sh

# Reboot device
reboot
