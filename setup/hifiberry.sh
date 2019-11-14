#!/usr/bin/env bash

if (whiptail --title "Install HifiBerry DAC Driver" --yesno "**** This will enable the hifiberry audio driver and disable the system audio driver ****" 12 78)
then setupHifiBerry; else echo "SKIP"; fi

setupHifiBerry(){
    echo "Installing HifiBerry..."
    echo "Done!"
}
