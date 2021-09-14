#!/usr/bin/bash
# Error management
set -o errexit
set -o pipefail
#set -o nounset

usage() {
cat 1>&2 <<EOF

This script configures a base Pi with OS updates, COIN73 software and gcloud tools

USAGE:
	`basename $0` [parameters]

PARAMETERS:
	-gn <NAME>  Your pretty name for git checking in. Default: "Nigel Johnson"
	-ge <EMAIL> Your git registered email address. Default: nigel@nigeljohnson.net
	-gp <PASS>  The Personal Access Token you made on github 

	-h | --help Show this help and exit

EOF
}
die() { [ -n "$1" ] && echo -e "\nError: $1\n" >&2; usage; [ -z "$1" ]; exit;}

GIT_USERNAME="Nigel Johnson"
GIT_USERMAIL="nigel@nigeljohnson.net"
GIT_PAT=""

if [ $# -eq 0 ]; then
	die
fi

while [[ $# -gt 0 ]]; do
	case $1 in
		-gp)
			GIT_PAT="$2"
			echo "GIT PAT: '$2'"
			shift
			;;
		-gn)
			GIT_USERNAME="$2"
			echo "GIT name: '$2'"
			shift
			;;
		-ge)
			GIT_USERMAIL="$2"
			echo "GIT email address: '$2'"
			shift
			;;
		-h|--help)
			usage
			exit 0
			;;
		*)
			die "Unknown option '$1'"
			;;
	esac
	shift
done

[ -z "$GIT_PAT" ] && die "PAT for git access not configured"
[ -z "$GIT_USERNAME" ] && die "git check-in name not configured"
[ -z "$GIT_USERMAIL" ] && die "git check-in email address not configured"
echo ""

echo "####################################################################"
echo "##"
echo "## THe configuration we will be using today:"
echo "##"
echo "##  git checkin name : '${GIT_USERNAME}'"
echo "## git email address : '${GIT_USERMAIL}'"
echo "##  git access token : '${GIT_PAT}'"
echo "##"
echo "####################################################################"
echo ""
echo "Shall we get started? Press return to continue"
echo ""
read ok

echo "####################################################################"
echo "##"
echo "## Update BIOS and core OS"
echo "##"
echo "####################################################################"
echo ""

# Ensure the base packages are up to date
echo "## Update core OS"
sudo apt update -y 
echo "## Ensure we have latest firmware available"
sudo apt full-upgrade -y
echo "## Cleanup loose packages"
sudo apt autoremove -y
echo "## Ensure we have latest firmware installed"
sudo rpi-eeprom-update

# Install core packages we need to do the core stuff later
echo "## Install core pacakges"
sudo apt install -y lsb-release apt-transport-https ca-certificates git python3-dev python3-pip screen

## Disable IPv6
echo "## Disabling IPv6"
sudo bash -c 'cat > /etc/sysctl.d/disable-ipv6.conf' << EOF
net.ipv6.conf.all.disable_ipv6 = 1
EOF

## Flashy login screen and path parameters
echo "## Install bashrc hooks"
bash -c 'cat >> ~/.bashrc' << EOF
source /webroot/coin73/res/bashrc
EOF

## Loop into rc.local
echo "## Install rc.local hooks"
sudo cat /etc/rc.local | grep -v 'exit 0' | sudo tee /etc/rc.local >/dev/null
sudo bash -c 'cat >> /etc/rc.local' << EOF
. /webroot/coin73/res/rc.local
exit 0
EOF

## Enable VNC service
echo "## Enable VNC service"
sudo ln -s /lib/systemd/system/vncserver-x11-serviced.service /etc/systemd/system/multi-user.target.wants/vncserver-x11-serviced.service

## Overclock the PI, and enable VNC server screen resolution in boot.config
echo "## Overclocking the PI and setting screen resolution to 1080p"
sudo bash -c 'cat >> /boot/config.txt' << EOF
over_voltage=6
arm_freq=2147
gpu_freq=750
hdmi_force_hotplug=1
hdmi_group=2
hdmi_mode=82
EOF

echo ""
echo "####################################################################"
echo ""
echo "Next we install the correct version of PHP"
echo ""
echo "Press return to continue"
echo ""
read ok

echo "####################################################################"
echo "## Update PHP to version 8, then downgrade to 7.4"
echo "##"
echo "####################################################################"
echo ""

# Update the package list with a repository that supports our needs and ensure we are up to date with that
echo "## Get repository signature"
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "## Install ARM repository for latest PHP builds"
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
echo "## Ensure we are up to date with that repository"
sudo apt update -y
echo "## Install out-of-date packages"
Sudo apt upgrade -y
echo "## Remove the latest PHP (v8)"
sudo apt remove -y --purge php8.0
echo "## Install the required version of PHP for gcloud (v7.4)"
sudo apt install -y php7.4 php7.4-BCMath php7.4-bz2 php7.4-Calendar php7.4-cgi php7.4-ctype php7.4-cURL php7.4-dba php7.4-dom php7.4-enchant php7.4-Exif php7.4-fileinfo php7.4-FTP php7.4-GD php7.4-gettext php7.4-GMP php7.4-iconv php7.4-intl php7.4-json php7.4-LDAP php7.4-mbstring php7.4-OPcache php7.4-Phar php7.4-posix php7.4-Shmop php7.4-SimpleXML php7.4-SOAP php7.4-Sockets php7.4-tidy php7.4-tokenizer php7.4-XML php7.4-XMLreader php7.4-XMLrpc php7.4-XMLwriter php7.4-XSL 
echo "## Cleanup loose packages"
sudo apt autoremove -y

echo ""
echo "####################################################################"
echo ""
echo "Next we install the gcloud SDK"
echo ""
echo "Press return to continue"
read ok

echo "####################################################################"
echo "##"
echo "## Install gcloud SDK and toolset"
echo "##"
echo "####################################################################"
echo ""

# Add the google source repository to our list to get the SDK software
echo "## Get repository signatures and install into repository manager"
echo "deb [signed-by=/usr/share/keyrings/cloud.google.gpg] https://packages.cloud.google.com/apt cloud-sdk main" | sudo tee -a /etc/apt/sources.list.d/google-cloud-sdk.list
curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | sudo apt-key --keyring /usr/share/keyrings/cloud.google.gpg add -
echo "## Install the google applications"
sudo apt-get install -y google-cloud-sdk google-cloud-sdk-datastore-emulator google-cloud-sdk-firestore-emulator 
echo "## Cleanup loose packages"
sudo apt-get autoremove -y

## Install composer
echo "## Install Composer for PHP"
cd /tmp
wget -O composer-setup.php https://getcomposer.org/installer
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
sudo composer self-update

echo ""
echo "####################################################################"
echo ""
echo "Next we install the COIN73 software"
echo ""
echo "Press return to continue"
echo ""
read ok

echo "####################################################################"
echo "##"
echo "## Install COIN73 software"
echo "##"
echo "####################################################################"
echo ""

# The software is held in github so set that up and clone it to the right place 
echo "## Cloning source code tree"
sudo mkdir /webroot
cd /webroot
git config --global credential.helper store
git config --global user.email $GIT_USERMAIL
git config --global user.name $GIT_USERNAME
sudo git clone https://${GIT_PAT}:x-oauth-basic@github.com/nigeljohnson73/coin73.git
sudo chown -R pi:pi coin73
cd coin73
cp res/install.config_localhost.php www/config_localhost.php
cp res/install.config_override.php www/config_override.php
cd api
ln -s ../www/config_override.php config_override.php
ln -s ../www/config_localhost.php config_localhost.php

## install the composer dependancies
echo "## Installing composer dependancies"
cd /webroot/coin73/www
composer install
cd /webroot/coin73/api
composer install

## Install crontab -entries to start the services
echo "## Installing service startup in crontab"
crontab -l | { cat; sudo bash -c 'cat' << EOF
@reboot sleep 30 && screen -S api -L -Logfile /tmp/api.php.log -dm bash -c "cd ~/webroot/coin73; php -S localhost:8085 -t api api/index.php"
@reboot sleep 30 && screen -S www -L -Logfile /tmp/www.php.log -dm bash -c "cd ~/webroot/coin73; php -S localhost:8080 -t www www/index.php"
EOF
} | crontab -

echo ""
echo "####################################################################"
echo ""
echo "Next, we will run the gcloud init. This will require browser"
echo "interaction so follow the on-screen prompts."
echo ""
echo "Press return to continue"
echo ""
read ok

echo ""
echo "####################################################################"
echo "##"
echo "## gcloud init"
echo "##"
echo "####################################################################"
echo ""

# Initialise the local repositories
echo "## Initialiising gcloud packages"
gcloud init

echo ""
echo "####################################################################"
echo ""
echo "Finally, we need to authenticate the service account credentials."
echo "This will require browser interaction so follow the on-screen "
echo "prompts."
echo ""
echo "Press return to continue"
echo ""
read ok

echo "####################################################################"
echo "##"
echo "## gcloud auth application-default login"
echo "##"
echo "####################################################################"
echo ""

# Authenticate the default service account
echo "## Authenticating default service account"
gcloud auth application-default login

echo ""
echo "####################################################################"
echo ""
echo "We are all done. Thanks for flying with us today and we value your"
echo "custom as we know you have choices, The next steps for you are:"
echo ""
echo " * Reboot this raspberry pi"
echo " * VNC onto the desktop"
echo ""
echo "Here are some useful commands you can run from a terminal:"
echo ""
echo " * 11t5-pull.sh   - Will update the codebase from git"
echo " * 11t5-deploy.sh - Will deploy the application to appspot"
echo ""
echo "####################################################################"
