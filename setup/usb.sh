#!/usr/bin/env bash

sudo apt-get install usbmount -y
sudo sed -i 's|MountFlags=slave|MountFlags=shared|g' /lib/systemd/system/systemd-udevd.service
