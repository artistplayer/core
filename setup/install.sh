#!/usr/bin/env bash

# Create User
wget -O -  https://github.com/artistplayer/core/raw/master/setup/user.sh | bash

# Install NGINX Server
wget -O -  https://github.com/artistplayer/core/raw/master/setup/server.sh | bash

# Install Composer
wget -O -  https://github.com/artistplayer/core/raw/master/setup/composer.sh | bash

# Install PHP
wget -O -  https://github.com/artistplayer/core/raw/master/setup/php.sh | bash

# Install USB Mounting Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/usb.sh | bash

# Install project
wget -O -  https://github.com/artistplayer/core/raw/master/setup/project.sh | bash

# Configure OMX Media player Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/omx.sh | bash

# Configure Bluetooth Tether Support
wget -O -  https://github.com/artistplayer/core/raw/master/setup/bluetooth.sh | bash

# Configure Device as Hotspot
#wget -O -  https://github.com/artistplayer/core/raw/master/setup/hotspot.sh | bash

# Install Raspotify
curl -sL https://dtcooper.github.io/raspotify/install.sh | sh

# Reboot device
reboot
