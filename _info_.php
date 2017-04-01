<?php
$mod_name="mgmt";
$mod_version="1.1";
$mod_path="/usr/share/fruitywifi/www/modules/$mod_name";
$mod_logs="$log_path/dnsmasq-mgmt.log";
$mod_logs_history="$mod_path/includes/logs/";
$mod_panel="show";
$mod_type="service";
$mod_alias="MGMT";
$mod_filter_hostapd_station="none";

# AP
//$mod_mgmt_io_in_action="";
$mod_mgmt_io_mode="1";
$mod_mgmt_ap_mode="1";
$mod_mgmt_io_in_iface="wlan0";
$mod_mgmt_io_in_set="1";
$mod_mgmt_io_in_ip="10.0.1.1";
$mod_mgmt_io_in_mask="255.255.255.0";
$mod_mgmt_io_in_gw="";
$mod_mgmt_io_out_iface="eth0";
$mod_mgmt_io_out_set="0";
$mod_mgmt_io_out_ip="192.168.0.1";
$mod_mgmt_io_out_mask="255.255.255.0";
$mod_mgmt_io_out_gw="192.168.0.1";
$mod_mgmt_hostapd_secure="0";
$mod_mgmt_hostapd_ssid="MGMT";
$mod_mgmt_hostapd_wpa_passphrase="FruityWifi";

# EXEC
$bin_sudo = "/usr/bin/sudo";
$bin_hostapd = "$mod_path/includes/hostapd";
$bin_hostapd_cli = "$mod_path/includes/hostapd_cli";
$bin_sh = "/bin/sh";
$bin_echo = "/bin/echo";
$bin_grep = "/usr/bin/ngrep";
$bin_killall = "/usr/bin/killall";
$bin_cp = "/bin/cp";
$bin_chmod = "/bin/chmod";
$bin_sed = "/bin/sed";
$bin_rm = "/bin/rm";
$bin_route = "/sbin/route";
$bin_perl = "/usr/bin/perl";

$bin_killall = "/usr/bin/killall";
$bin_ifconfig = "/sbin/ifconfig";
$bin_iptables = "/sbin/iptables";
$bin_dnsmasq = "/usr/sbin/dnsmasq";
$bin_sed = "/bin/sed";
$bin_echo = "/bin/echo";
$bin_rm = "/bin/rm";
$bin_mv = "/bin/mv";

# ISUP
$mod_isup="ps auxww | grep -iEe 'hostapd.+$mod_name.+hostapd|airbase-ng -e' | grep -v -e grep";
?>
