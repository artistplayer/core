# Installation

1. Write the latest Lite version of Raspberry onto the SD card (https://www.raspberrypi.org/documentation/installation/installing-images/README.md).
2. Add a empty file with the name 'ssh' on the SD card.
3. Connect the Raspberry on the router with an ethernet cable.
4. Boot the device, and login over SSH with user: pi, pass: raspberry
5. Execute the following command:
```bash
 curl -H 'Cache-Control: no-cache' -sL https://github.com/artistplayer/core/raw/master/setup/install.sh | sh
```
