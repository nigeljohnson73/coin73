#!/bin/bash
# The script configures simultaneous AP and Managed Mode Wifi on Raspberry Pi Zero W (should also work on Raspberry Pi 3)
# Retrieved from https://github.com/lukicdarkoo/rpi-wifi
# Licence: GPLv3
# Author: Darko Lukic <lukicdarkoo@gmail.com>
# Special thanks to: https://albeec13.github.io/2017/09/26/raspberry-pi-zero-w-simultaneous-ap-and-managed-mode-wifi/

# Error management
set -o errexit
set -o pipefail
set -o nounset

usage() {
    cat 1>&2 <<EOF
Configures simultaneous AP and Managed Mode Wifi on Raspberry Pi

USAGE:
    rpi-wifi -a <ap_ssid> [<ap_password>] -c <client_ssid> [<client_password>]
    
    rpi-wifi -a MyAP myappass -c MyWifiSSID mywifipass

PARAMETERS:
    -a, --ap      	AP SSID & password
    -c, --client	Client SSID & password
	-x, --country	Client Country code (GB for example)
    -i, --ip        AP IP
    -d, --dns       AP DNS IP
	-f, --channel	AP Radio frequency channel (6 for example)
	-l, --lan       wlan interface to setup on

FLAGS:
	-o, --open			Allow traffic when VPN is down
    -h, --help          Show this help
EOF
    exit 0
}

OPENCNT=\#
DNS_IP="8.8.8.8"
CLIENT_COUNTRY="GB"
AP_IP="10.10.1.1"
AP_CHANNEL=6
AP_WLAN=1

POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"

case $key in
    -c|--client)
    CLIENT_SSID="$2"
    CLIENT_PASSPHRASE="$3"
    shift
    shift
    shift
    ;;
    -a|--ap)
    AP_SSID="$2"
    AP_PASSPHRASE="$3"
    shift
    shift
    shift
    ;;
    -i|--ip)
    AP_IP="$2"
    shift
    shift
    ;;
    -l|--lan)
    AP_WLAN="$2"
    shift
    shift
    ;;
    -x|--country)
    CLIENT_COUNTRY="$2"
    shift
    shift
    ;;
    -d|--dns)
    DNS_IP="$2"
    shift
    shift
    ;;
    -f|--channel)
    AP_CHANNEL="$2"
    shift
    shift
    ;;
    -h|--help)
    usage
    shift
	;;
    -o|--open)
    echo "Setting unclear traffic options"
    OPENCNT=""
    shift
    ;;
    *)
    POSITIONAL+=("$1")
    shift
    ;;
esac
done
set -- "${POSITIONAL[@]}"

[ $AP_SSID ] || usage

AP_IP_BEGIN=`echo "${AP_IP}" | sed -e 's/\.[0-9]\{1,3\}$//g'`
MAC_ADDRESS="$(cat /sys/class/net/wlan${AP_WLAN}/address)"

# Install dependencies
# sudo apt -y update && sudo apt -y full-upgrade
sudo apt -y install dnsmasq dhcpcd hostapd cron

# Populate `/etc/udev/rules.d/70-persistent-net.rules`
sudo bash -c 'cat > /etc/udev/rules.d/70-persistent-net.rules' << EOF
SUBSYSTEM=="ieee80211", ACTION=="add|change", ATTR{macaddress}=="${MAC_ADDRESS}", KERNEL=="phy${AP_WLAN}",\
 RUN+="/sbin/iw phy phy${AP_WLAN} interface add ap0 type __ap",\
 RUN+="/bin/ip link set ap0 address ${MAC_ADDRESS}
EOF

# Populate `/etc/dnsmasq.conf`
sudo bash -c 'cat > /etc/dnsmasq.conf' << EOF
interface=lo,ap0
no-dhcp-interface=lo,wlan0,wlan1
bind-interfaces
server=${DNS_IP}
domain-needed
bogus-priv
dhcp-range=${AP_IP_BEGIN}.20,${AP_IP_BEGIN}.254,12h
EOF

# Update `/etc/default/dnsmasq` to stop nameserver being attached to the lo interface
# Fixes https://github.com/lukicdarkoo/rpi-wifi/issues/23
cat /etc/default/dnsmasq | grep -v 'DNSMASQ_EXCEPT=lo' | sudo tee /etc/default/dnsmasq
sudo bash -c 'cat >> /etc/default/dnsmasq' << EOF
DNSMASQ_EXCEPT=lo
EOF

# Populate `/etc/hostapd/hostapd.conf`
sudo bash -c 'cat > /etc/hostapd/hostapd.conf' << EOF
ctrl_interface=/var/run/hostapd
ctrl_interface_group=0
interface=ap0
#driver=nl80211
ssid=${AP_SSID}
hw_mode=g
channel=${AP_CHANNEL}
wmm_enabled=0
macaddr_acl=0
auth_algs=1
wpa=2
$([ $AP_PASSPHRASE ] && echo "wpa_passphrase=${AP_PASSPHRASE}")
wpa_key_mgmt=WPA-PSK
wpa_pairwise=TKIP CCMP
rsn_pairwise=CCMP
EOF

# Populate `/etc/default/hostapd`
sudo bash -c 'cat > /etc/default/hostapd' << EOF
DAEMON_CONF="/etc/hostapd/hostapd.conf"
EOF

# Populate `/etc/wpa_supplicant/wpa_supplicant.conf`
sudo bash -c 'cat > /etc/wpa_supplicant/wpa_supplicant.conf' << EOF
country=${CLIENT_COUNTRY}
ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1

network={
    ssid="${CLIENT_SSID}"
    $([ $CLIENT_PASSPHRASE ] && echo "psk=\"${CLIENT_PASSPHRASE}\"")
    id_str="AP1"
}
EOF

# Populate `/etc/network/interfaces`
sudo bash -c 'cat > /etc/network/interfaces' << EOF
source-directory /etc/network/interfaces.d

auto lo
auto ap0
auto wlan0
auto wlan1

iface lo inet loopback

allow-hotplug ap0
iface ap0 inet static
    address ${AP_IP}
    netmask 255.255.255.0
    hostapd /etc/hostapd/hostapd.conf

allow-hotplug wlan0
iface wlan0 inet manual
    wpa-roam /etc/wpa_supplicant/wpa_supplicant.conf

allow-hotplug wlan1
iface wlan1 inet manual

iface AP1 inet dhcp
EOF

echo "Send in clear flag: '$OPENCNT'"
# Populate `/bin/rpi-wifi.sh`
sudo bash -c 'cat > /bin/rpi-wifi.sh' << EOF
#!/bin/bash

echo 'Starting Wifi AP and client...'

sleep 5
echo 'Resetting interfaces...'
sudo ifdown --force wlan${AP_WLAN}
sudo ifdown --force ap0

sleep 2
echo 'Restarting interfaces...'
sudo ifup ap0
sleep 1
sudo ifup wlan${AP_WLAN}

if [ -n "\$1" ]; then
	echo 'Applying routing...'
	sudo sysctl -w net.ipv4.ip_forward=1
	${OPENCNT}sudo iptables -t nat -A POSTROUTING -o wlan0 -j MASQUERADE # Used if you want non-VPN traffic to work
	sudo iptables -t nat -A POSTROUTING -o tun0 -j MASQUERADE # Used if you want traffic routed over VPN when connected
	sudo iptables -A OUTPUT -p udp --dport 53 -d ${DNS_IP} -j ACCEPT # Bypass expressvpn dumping DNS traffic
	expressvpn connect > /dev/null 2>&1
fi

echo 'Restarting dnsmasq service...'
sudo systemctl restart dnsmasq

echo 'AP and client configuration complete'
EOF
sudo chmod +x /bin/rpi-wifi.sh

# Make the interface script kick off in root scope within rc.local rather than in pi cron
sudo cat /etc/rc.local | grep -v 'exit 0' | sudo tee /etc/rc.local >/dev/null
sudo bash -c 'cat >> /etc/rc.local' << EOF
/bin/rpi-wifi.sh -routing &
exit 0
EOF

# Finish
echo "Wifi configuration is finished! Please reboot your Raspberry Pi to apply changes..."
