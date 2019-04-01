#!/usr/bin/env bash



# Create User
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/user.sh | sh

# Install NGINX Server
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/server.sh | sh

# Install Composer
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/composer.sh | sh

# Install PHP
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/php.sh | sh

# Install USB Mounting Support
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/usb.sh | sh

# Install project
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/project.sh | sh

# Configure OMX Media player Support
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/omx.sh | sh

# Configure Bluetooth Tether Support
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/bluetooth.sh | sh

# Configure Device as Hotspot
curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/hotspot.sh | sh

# Install Raspotify
curl -sL https://dtcooper.github.io/raspotify/install.sh | sh

# Reboot device
sudo reboot
