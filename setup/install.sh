#!/usr/bin/env bash

# Create User
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/user.sh > /dev/null | bash

# Install NGINX Server
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/server.sh > /dev/null | bash

# Install Composer
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/composer.sh > /dev/null | bash

# Install PHP
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/php.sh > /dev/null | bash

# Install USB Mounting Support
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/usb.sh > /dev/null | bash

# Install project
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/project.sh > /dev/null | bash

# Configure OMX Media player Support
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/omx.sh > /dev/null | bash

# Configure Bluetooth Tether Support
wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/bluetooth.sh > /dev/null | bash

# Configure Device as Hotspot
#wget --no-cache -q -O -  https://github.com/artistplayer/core/raw/master/setup/hotspot.sh > /dev/null | bash

# Install Raspotify
curl -sL https://dtcooper.github.io/raspotify/install.sh > /dev/null | sh

# Reboot device
reboot
