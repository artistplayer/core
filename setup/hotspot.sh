#!/usr/bin/env bash

sudo apt-get install hostapd dnsmasq -y


sudo sh -c 'echo "
source-directory /etc/network/interfaces.d

auto lo
iface lo inet loopback
iface eth0 inet manual
allow-hotplug wlan0
auto wlan0
iface wlan0 inet static
address 192.168.1.1
netmask 255.255.255.0
" > /etc/network/interfaces'

isInFile=$(cat /etc/hosts | grep -c "artist.player")
if [ $isInFile -eq 0 ]; then
    sudo sh -c 'echo "
192.168.1.1	artist.player
" >> /etc/hosts'
fi


sudo sh -c 'echo "
interface=wlan0
bridge=br0
hw_mode=g
channel=7
wmm_enabled=0
macaddr_acl=0
auth_algs=1
ignore_broadcast_ssid=0
wpa=2
wpa_key_mgmt=WPA-PSK
wpa_pairwise=TKIP
rsn_pairwise=CCMP
ssid=ArtistPlayer
wpa_passphrase=welcomeartist
" > /etc/hostapd/hostapd.conf'



sudo sh -c 'echo "
    DAEMON_CONF=\"/etc/hostapd/hostapd.conf\"
" > /etc/default/hostapd'



sudo mv /etc/dnsmasq.conf /etc/dnsmasq.conf.bak


sudo sh -c 'echo "
bogus-priv
server=/player/192.168.1.1
local=/player/
address=/#/192.168.1.1
interface=wlan0
domain=player
dhcp-range=192.168.1.10,192.168.1.254,1h
dhcp-option=3,192.168.1.1
dhcp-option=6,192.168.1.1
dhcp-authoritative
" > /etc/dnsmasq.conf'

sudo service networking restart
sudo systemctl unmask hostapd
sudo systemctl enable hostapd
sudo systemctl start hostapd
sudo service dnsmasq restart
