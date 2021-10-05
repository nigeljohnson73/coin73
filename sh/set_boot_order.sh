#!/bin/bash

usage() {
cat 1>&2 <<EOF

This script configures the first boot device. The assumption is that you have a
recent enough bootloader. Don't be here if you don't know what that means.

USAGE:
	`basename $0` USB|SD

EOF
}
die() { [ -n "$1" ] && echo "\nError: $1" >&2; usage; [ -z "$1" ]; exit;}

boot_order=""

if [ $# -eq 0 ]; then
	die
fi

while [[ $# -gt 0 ]]; do
	case $1 in
		usb)
			boot_order="0xf14"
			shift
			;;
		USB)
			boot_order="0xf14"
			shift
			;;
		sd)
			boot_order="0xf41"
			shift
			;;
		SD)
			boot_order="0xf41"
			shift
			;;
		-h|--help)
			die
			;;
		*)
			die "Unknown option '$1'"
			;;
	esac
	shift
done

if [ -z "$boot_order" ]
then
	die "No boot order provided"
fi

cat > /tmp/boot.conf << EOF
[all]
BOOT_UART=0
WAKE_ON_GPIO=1
ENABLE_SELF_UPDATE=1
BOOT_ORDER=${boot_order}
EOF

#cat /tmp/boot.conf
sudo rpi-eeprom-config --apply /tmp/boot.conf
rm -f /tmp/boot.conf