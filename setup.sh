#!/usr/bin/env bash
SECUREKEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
PASSWORD=$(echo ${SECUREKEY}|cut -c 1-8)
IPADDR=$(ip a s|sed -ne '/127.0.0.1/!{s/^[ \t]*inet[ \t]*\([0-9.]\+\)\/.*$/\1/p}')

# Defaults
HOST=127.0.0.1
PORT=80
SOCK=9000

# Screens
welcome(){
	if (whiptail --title "Confirm Installation" \
    --yesno "**** This will install the Signalize Service on your device ****\n
    If you continue, the Signalize Server will be installed." 12 78)
    then doInstall; else exit; fi
}

doInstall(){
        echo "Installing PHP-FPM..."
        sudo apt-get install ca-certificates apt-transport-https -y >/dev/null 2>&1
        sudo wget -q https://packages.sury.org/php/apt.gpg -O- | sudo apt-key add - >/dev/null 2>&1
        sudo echo "deb https://packages.sury.org/php/ stretch main" | sudo tee /etc/apt/sources.list.d/php7.list >/dev/null 2>&1
        sudo apt-get update -y >/dev/null 2>&1
		sudo apt-get install php7.3-fpm php7.3-opcache php7.3-curl php7.3-mbstring php7.3-pgsql php7.3-zip php7.3-xml php7.3-gd php7.3-sqlite3 usbmount git omxplayer python-dbus rng-tools hostapd -y >/dev/null 2>&1

		sudo sed -i 's|MountFlags=slave|MountFlags=shared|g' /lib/systemd/system/systemd-udevd.service

        echo "Setup user environment..."
        setupUser


        echo "Installing Composer..."
        setupComposer

        echo "Clean Composer Cache..."
        clearComposerCache

	    echo "Installing Signalize..."
        setupPackages

        echo "Install & Configure NGINX Server..."
        setupNginX

        echo "Configure Service..."
        setupService
}

setupUser(){
    if !(id "signalize" >/dev/null 2>&1); then
        echo "* Create user"
        sudo useradd signalize -p $(openssl passwd -crypt $PASSWORD) -m -d /home/signalize -G sudo,dialout,audio,video
    fi
}

setupComposer(){
    cd /home/signalize
    if !([ -f "/home/signalize/composer.phar" ]); then
        sudo su - signalize -c "
        php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\" &&
        php -r \"if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;\" &&
        php composer-setup.php &&
        php -r \"unlink('composer-setup.php');\"" >/dev/null 2>&1
    fi
}

clearComposerCache(){
		cd /home/signalize
		sudo su - signalize -c "php composer.phar clearcache" >/dev/null 2>&1
}

setupPackages(){
    cd /home/signalize

    sudo rm -Rf core >/dev/null 2>&1
    sudo su - signalize -c "git clone https://github.com/artistplayer/core.git core" >/dev/null 2>&1
    sudo su - signalize -c "cd core && php ../composer.phar install"
    sudo su - signalize -c "cd core && cp .env.example .env"
    sudo su - signalize -c "touch core/database/database.sqlite" >/dev/null 2>&1
    sudo chmod 0777 core/database -Rf >/dev/null 2>&1
    sudo chmod 0777 core/storage -Rf >/dev/null 2>&1
    sudo su - signalize -c "cd core && php artisan migrate"
    sudo su - signalize -c "cd core && php artisan storage:link"
    sudo su - signalize -c "cd core && php artisan optimize"
    curl -sL https://dtcooper.github.io/raspotify/install.sh | sh
}

setupNginX(){
    sudo apt-get install nginx -y

    cd /home/signalize
    sudo su - signalize -c "echo 'server {
        listen $PORT;
        listen [::]:$PORT;

        server_name $HOST;

		root /home/signalize/core/public;
		location /api/ {
                try_files \$uri \$uri/ /index.php?\$query_string;
        }

        location ~ \.php\$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.3-fpm.sock;
        }

        location /storage/ {
	        try_files \$uri \$uri/ =404;
        }

        location /sock/ {
            proxy_pass http://127.0.0.1:$SOCK\$uri\$is_args\$args;
            proxy_http_version 1.1;
            proxy_set_header Upgrade \$http_upgrade;
            proxy_set_header Connection \"Upgrade\";
        }

        location / {
            root /home/signalize/core/vendor/artistplayer/app/public;
			index index.html;
            try_files \$uri \$uri/ /index.html;
        }

    }' > signalize.conf" >/dev/null 2>&1

    sudo rm /etc/nginx/sites-enabled/default
    sudo ln -s /home/signalize/signalize.conf /etc/nginx/sites-enabled/default
    sudo systemctl reload nginx >/dev/null 2>&1
}
setupService(){
    if [ -f /etc/systemd/system/signalize.service ]; then
        sudo systemctl stop signalize >/dev/null 2>&1
        sudo systemctl disable signalize >/dev/null 2>&1
    fi

    cd /home/signalize
    sudo su - signalize -c "echo '[Unit]
    Description=Signalize Socket Service
    After=network.target
    StartLimitIntervalSec=0
    [Service]
    Type=simple
    Restart=always
    RestartSec=1
    User=signalize
    WorkingDirectory=/home/signalize/core
    ExecStart=/usr/bin/php artisan socket server

    [Install]
    WantedBy=multi-user.target
    ' > socket-server.service" >/dev/null 2>&1


    sudo su - signalize -c "echo '[Unit]
    Description=Signalize OMX Service
    After=network.target
    StartLimitIntervalSec=0
    [Service]
    Type=simple
    Restart=always
    RestartSec=1
    User=signalize
    WorkingDirectory=/home/signalize/core
    ExecStart=/usr/bin/php artisan socket omx

    [Install]
    WantedBy=multi-user.target
    ' > socket-omx.service" >/dev/null 2>&1


    sudo ln -s /home/signalize/socket-server.service /etc/systemd/system/socket-server.service &&
    sudo ln -s /home/signalize/socket-omx.service /etc/systemd/system/socket-omx.service &&
    sudo systemctl start socket-server &&
    sudo systemctl start socket-omx &&
    sudo systemctl enable socket-server &&
    sudo systemctl enable socket-omx >/dev/null 2>&1
}

finish(){
    whiptail --msgbox --backtitle "Installation Complete" \
    --title "Installation Complete" \
    "You're almost done!\n\n
        Go to the website to complete the installation:
        Web address: http://${IPADDR}$(if [ $PORT != "80" ]; then echo ":${PORT}"; fi)/" \
    12 78
}


# Install Signalize
welcome
finish
reboot