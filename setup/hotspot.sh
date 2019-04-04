#!/usr/bin/env bash

sudo apt-get install hostapd dnsmasq -y


sudo sh -c 'echo "
source-directory /etc/network/interfaces.d

auto lo
iface lo inet loopback

auto eth0
iface eth0 inet dhcp

allow-hotplug wlan0
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
# WifI interface and driver to be used
interface=wlan0
driver=nl80211

# WiFi settings
hw_mode=g
channel=6
ieee80211n=1
wmm_enabled=1
ht_capab=[HT40][SHORT-GI-20][DSSS_CCK-40]
macaddr_acl=0
ignore_broadcast_ssid=0

# Use WPA authentication and a pre-shared key
auth_algs=1
wpa=2
wpa_key_mgmt=WPA-PSK
rsn_pairwise=CCMP

# Network Name
ssid=ArtistPlayer
# Network password
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
sudo update-rc.d hostapd defaults