#!/usr/bin/env bash

if (whiptail --title "Install HifiBerry DAC Driver" --yesno "**** This will enable the hifiberry audio driver and disable the system audio driver ****" 12 78)
then setupHifiBerry; else echo "SKIP"; fi

setupHifiBerry(){
    echo "Installing HifiBerry..."
    
    sudo sed -i 's/dtparam=audio=on/#dtparam=audio=on/g' /boot/config.txt
    sudo su -c "printf '\n[hifiberry]\ndtoverlay=hifiberry-dacplus' >> /boot/config.txt"
    
    echo "Done!"
}
