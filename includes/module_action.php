<? 
/*
    Copyright (C) 2013-2017 xtr4nge [_AT_] gmail.com

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 
?>
<?
include "../../../login_check.php";
include "../../../config/config.php";
include "../_info_.php";
include "../../../functions.php";

//include "options_config.php";

// Checking POST & GET variables...
if ($regex == 1) {
    regex_standard($_GET["service"], "../msg.php", $regex_extra);
    regex_standard($_GET["action"], "../msg.php", $regex_extra);
    regex_standard($_GET["page"], "../msg.php", $regex_extra);
    regex_standard($_GET["install"], "../msg.php", $regex_extra);
}

$service = $_GET['service'];
$action = $_GET['action'];
$page = $_GET['page'];
$install = $_GET['install'];

function hostapdStationWhitelist($path_hostapd_conf, $path_filter_conf) {
    global $bin_sed;
    global $bin_echo;
    
    $is_present = exec("grep -iEe '^#macaddr_acl' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's/#macaddr_acl.*/macaddr_acl=1/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^macaddr_acl' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\nmacaddr_acl=1' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }
    
    $is_present = exec("grep -iEe '^#accept_mac_file' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's,#accept_mac_file.*,accept_mac_file=$path_filter_conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^accept_mac_file' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\naccept_mac_file=$path_filter_conf' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }

}

function hostapdStationBlacklist($path_hostapd_conf, $path_filter_conf) {
    global $bin_sed;
    global $bin_echo;
    
    $is_present = exec("grep -iEe '^#macaddr_acl' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's/#macaddr_acl.*/macaddr_acl=0/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^macaddr_acl' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\nmacaddr_acl=0' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }
    
    $is_present = exec("grep -iEe '^#deny_mac_file' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's,#deny_mac_file.*,deny_mac_file=$path_filter_conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^deny_mac_file' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\ndeny_mac_file=$path_filter_conf' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }
}

function flushIptables() {	
	global $bin_iptables;
	
	$exec = "$bin_iptables -F";
	exec_fruitywifi($exec);
	$exec = "$bin_iptables -t nat -F";
	exec_fruitywifi($exec);
	$exec = "$bin_iptables -t mangle -F";
	exec_fruitywifi($exec);
	$exec = "$bin_iptables -X";
	exec_fruitywifi($exec);
	$exec = "$bin_iptables -t nat -X";
	exec_fruitywifi($exec);
	$exec = "$bin_iptables -t mangle -X";
	exec_fruitywifi($exec);
	echo $exec;
}

function setNetworkManager() {
	
	global $mod_mgmt_io_in_iface;
	global $bin_sed;
	global $bin_echo;
	
	$exec = "macchanger --show $mod_mgmt_io_in_iface |grep 'Permanent'";
	exec($exec, $output);
	$mac = explode(" ", $output[0]);
	
	$exec = "grep '^unmanaged-devices' /etc/NetworkManager/NetworkManager.conf";
	$ispresent = exec($exec);
	
	$exec = "$bin_sed -i '/unmanaged/d' /etc/NetworkManager/NetworkManager.conf";
	exec_fruitywifi($exec);
	$exec = "$bin_sed -i '/\[keyfile\]/d' /etc/NetworkManager/NetworkManager.conf";
	exec_fruitywifi($exec);
	
	if ($ispresent == "") {
		$exec = "$bin_echo '[keyfile]' >> /etc/NetworkManager/NetworkManager.conf";
		exec_fruitywifi($exec);

		$exec = "$bin_echo 'unmanaged-devices=mac:".$mac[2].";interface-name:".$mod_mgmt_io_in_iface."' >> /etc/NetworkManager/NetworkManager.conf";
		exec_fruitywifi($exec);
	}
	
}

function cleanNetworkManager() {
	
	global $bin_sed;
	
	// REMOVE lines from NetworkManager
	$exec = "$bin_sed -i '/unmanaged/d' /etc/NetworkManager/NetworkManager.conf";
	exec_fruitywifi($exec);
	$exec = "$bin_sed -i '/\[keyfile\]/d' /etc/NetworkManager/NetworkManager.conf";
	exec_fruitywifi($exec);
}

function killRegex($regex){
	
	$exec = "ps aux|grep -iEe '$regex' | grep -v grep | awk '{print $2}'";
	exec($exec,$output);
	
	if (count($output) > 0) {
		$exec = "kill " . $output[0];
		exec_fruitywifi($exec);
	}
	
}

function copyLogsHistory() {
	
	global $bin_cp;
	global $bin_mv;
	global $mod_logs;
	global $mod_logs_history;
	global $bin_echo;
	
	if ( 0 < filesize( $mod_logs ) ) {
		$exec = "$bin_cp $mod_logs $mod_logs_history/".gmdate("Ymd-H-i-s").".log";
		exec_fruitywifi($exec);
		
		$exec = "$bin_echo '' > $mod_logs";
		exec_fruitywifi($exec);
	}
}

// HOSTAPD
if($service != "") {
	if ($action == "start") {
		
		// SETUP NetworkManager
		setNetworkManager();
		
		$exec = "$bin_ifconfig $mod_mgmt_io_in_iface down";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $mod_mgmt_io_in_iface 0.0.0.0";
		exec_fruitywifi($exec);

		killRegex("hostapd.+$mod_name.+hostapd");
		
		$exec = "$bin_rm /var/run/hostapd-phy0/$mod_mgmt_io_in_iface";
		exec_fruitywifi($exec);

		killRegex("dnsmasq.+$mod_name.+dnsmasq");
		
		$exec = "$bin_ifconfig $mod_mgmt_io_in_iface up";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $mod_mgmt_io_in_iface up $mod_mgmt_io_in_ip netmask 255.255.255.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_echo 'nameserver $mod_mgmt_io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
		exec_fruitywifi($exec);
		
		$exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		$exec = "$bin_dnsmasq -C $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
		
		// SET FILTER BLACKLIST|WHITELIST
		$path_filter_conf = "/usr/share/fruitywifi/conf/mgmt-filter.conf";
		
		// SET HOSTAPD CONF
        if ($mod_mgmt_hostapd_secure == 1) {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "$mod_path/includes/conf/hostapd-secure.conf";
        } else {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "$mod_path/includes/conf/hostapd.conf";
        }
        
        // SET HOSTAPD [BLACK|WHITE]
        $exec = "$bin_sed -i 's/^macaddr_acl.*/#macaddr_acl=0/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^accept_mac_file.*,#accept_mac_file=$path_filter_conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^deny_mac_file.*,#deny_mac_file=$path_filter_conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
		
		if ($mod_filter_hostapd_station == "blacklist") {
            hostapdStationBlacklist($path_hostapd_conf, $path_filter_conf);
        } else if ($mod_filter_hostapd_station == "whitelist") {
            hostapdStationWhitelist($path_hostapd_conf, $path_filter_conf);
        }
	
		//Verifies if karma-hostapd is installed
		if ($mod_mgmt_hostapd_secure == 1) {
			
			//REPLACE SSID
			$exec = "$bin_sed -i 's/^ssid=.*/ssid=".$mod_mgmt_hostapd_ssid."/g' $mod_path/includes/conf/hostapd-secure.conf";
			exec_fruitywifi($exec);
			
			//REPLACE IFACE                
			$exec = "$bin_sed -i 's/^interface=.*/interface=".$mod_mgmt_io_in_iface."/g' $mod_path/includes/conf/hostapd-secure.conf";
			exec_fruitywifi($exec);
			
			//REPLACE WPA_PASSPHRASE
			$exec = "$bin_sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$mod_mgmt_hostapd_wpa_passphrase."/g' $mod_path/includes/conf/hostapd-secure.conf";
			exec_fruitywifi($exec);
			
			//EXTRACT MACADDRESS
			unset($output);
			$output = getIfaceMAC($mod_mgmt_io_in_iface);
			
			//REPLACE MAC
			//$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd-secure.conf";
			$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' $mod_path/includes/conf/hostapd-secure.conf";
			exec_fruitywifi($exec);
			/*
			print_r($output);
			echo "<br>";
			echo $exec;
			exit;
			*/
			$exec = "/usr/sbin/hostapd -P /var/run/hostapd-phy0 -B $mod_path/includes/conf/hostapd-secure.conf";
		} else {
			
			//REPLACE SSID
			$exec = "$bin_sed -i 's/^ssid=.*/ssid=".$mod_mgmt_hostapd_ssid."/g' $mod_path/includes/conf/hostapd.conf";
			exec_fruitywifi($exec);
			
			//REPLACE IFACE
			$exec = "$bin_sed -i 's/^interface=.*/interface=".$mod_mgmt_io_in_iface."/g' $mod_path/includes/conf/hostapd.conf";
			exec_fruitywifi($exec);
			
			//REPLACE WPA_PASSPHRASE
			$exec = "$bin_sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$mod_mgmt_hostapd_wpa_passphrase."/g' $mod_path/includes/conf/hostapd.conf";
			exec_fruitywifi($exec);
			
			//EXTRACT MACADDRESS
			unset($output);
			$output = getIfaceMAC($mod_mgmt_io_in_iface);
			
			//REPLACE BSSID
			//$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd.conf";
			$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' $mod_path/includes/conf/hostapd.conf";
			exec_fruitywifi($exec);
			
			$exec = "/usr/sbin/hostapd -P /var/run/hostapd-phy0 -B $mod_path/includes/conf/hostapd.conf";
		}
		exec_fruitywifi($exec);

		// IPTABLES	FLUSH	
		flushIptables();
		
		$exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
		exec_fruitywifi($exec);
		$exec = "$bin_iptables -t nat -A POSTROUTING -o $mod_mgmt_io_out_iface -j MASQUERADE";
		exec_fruitywifi($exec);
		
		// CLEAN DHCP log
		$exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases.mgmt";
		exec_fruitywifi($exec);

	} else if($action == "stop") {

		// REMOVE lines from NetworkManager
		cleanNetworkManager();

		killRegex("hostapd.+$mod_name.+hostapd");
		
		$exec = "$bin_rm /var/run/hostapd-phy0/$mod_mgmt_io_in_iface";
		exec_fruitywifi($exec);

		$exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);

		killRegex("dnsmasq.+$mod_name.+dnsmasq");
		
		$exec = "ip addr flush dev $mod_mgmt_io_in_iface";
		exec_fruitywifi($exec);
		
		$exec = "$bin_ifconfig $mod_mgmt_io_in_iface down";
		exec_fruitywifi($exec);
		
		// IPTABLES	FLUSH	
		flushIptables();
		
		// LOGS COPY
		copyLogsHistory();
		
	}
}

// AIRCRACK
if($service != "" and $mod_mgmt_ap_mode == "2") { // AIRCRACK (airbase-ng)
	if ($action == "start") {

		$exec = "/usr/bin/sudo /usr/sbin/airmon-ng stop mon0";
		exec_fruitywifi($exec);
	
		$exec = "$bin_killall airbase-ng";
		exec_fruitywifi($exec);
	
		killRegex("airbase-ng");
	
		//$exec = "$bin_killall dnsmasq";
		//exec_fruitywifi($exec);
		
		killRegex("dnsmasq.+$mon_name.+dnsmasq");
		
		$exec = "$bin_echo 'nameserver $mod_mgmt_io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
		exec_fruitywifi($exec);
		
		$exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		// SETUP NetworkManager
		setNetworkManager();
					
		$exec = "/usr/bin/sudo /usr/sbin/airmon-ng start $mod_mgmt_io_in_iface";
		exec_fruitywifi($exec);
		
		$exec = "/usr/sbin/airbase-ng -e $mod_mgmt_hostapd_ssid -c 2 mon0 > /tmp/airbase.log &"; //-P (all)
		exec_fruitywifi($exec);

		$exec = "sleep 1";
		exec_fruitywifi($exec);

		$exec = "$bin_ifconfig at0 up";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig at0 up $mod_mgmt_io_in_ip netmask 255.255.255.0";
		exec_fruitywifi($exec);

		$exec = "$bin_dnsmasq -C $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
		
		// IPTABLES	FLUSH	
		flushIptables();
		
		$exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
		exec_fruitywifi($exec);
		$exec = "$bin_iptables -t nat -A POSTROUTING -o $mod_mgmt_io_out_iface -j MASQUERADE";
		exec_fruitywifi($exec);
		
		// CLEAN DHCP log
		$exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
		exec_fruitywifi($exec);

	} else if($action == "stop") {

		// REMOVE lines from NetworkManager
		cleanNetworkManager();

		$exec = "$bin_killall airbase-ng";
		exec_fruitywifi($exec);

		killRegex("airbase-ng");
		
		$exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);

		killRegex("dnsmasq.+$mod_name.+dnsmasq");
		
		$exec = "/usr/bin/sudo /usr/sbin/airmon-ng stop mon0";
		exec_fruitywifi($exec);

		$exec = "ip addr flush dev at0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_ifconfig at0 down";
		exec_fruitywifi($exec);

		// IPTABLES	FLUSH	
		flushIptables();
		
		// LOGS COPY
		copyLogsHistory();
		
	}
}

if ($install == "install_$mod_name") {

    $exec = "chmod 755 install.sh";
    exec_fruitywifi($exec);

    $exec = "$bin_sudo ./install.sh > $log_path/install.txt &";
    exec_fruitywifi($exec);

    header('Location: ../../install.php?module='.$mod_name);
    exit;
}

if ($page == "status") {
    header('Location: ../../../action.php');
} else {
    header('Location: ../../action.php?page='.$mod_name);
}

?>
