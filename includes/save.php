<? 
/*
	Copyright (C) 2013-2015 xtr4nge [_AT_] gmail.com

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

include "../../../config/config.php";
include "../_info_.php";
include "../../../login_check.php";
include "../../../functions.php";

include "options_config.php";

// Checking POST & GET variables...
if ($regex == 1) {
	regex_standard($_POST['type'], "../../../msg.php", $regex_extra);
}

$type = $_POST['type'];

$io_in_iface = $_POST['io_in_iface'];
$io_in_ip = $_POST['io_in_ip'];
$hostapd_ssid = $_POST['hostapd_ssid'];
$hostapd_wpa_passphrase = $_POST['hostapd_wpa_passphrase'];
$hostapd_secure = $_POST['hostapd_secure'];


$io_mode = $_POST["io_mode"];
$ap_mode = $_POST["ap_mode"];

$io_in_iface = $_POST["io_in_iface"];
$io_in_set = $_POST["io_in_set"];
$io_in_ip = $_POST["io_in_ip"];
$io_in_mask = $_POST["io_in_mask"];
$io_in_gw = $_POST["io_in_gw"];

$io_out_iface = $_POST["io_out_iface"];
$io_out_set = $_POST["io_out_set"];
$io_out_ip = $_POST["io_out_ip"];
$io_out_mask = $_POST["io_out_mask"];
$io_out_gw = $_POST["io_out_gw"];

$hostapd_secure = $_POST["hostapd_secure"];
$hostapd_ssid = $_POST["hostapd_ssid"];
$hostapd_wpa_passphrase = $_POST["hostapd_wpa_passphrase"];

// ------------ IN | OUT (START) -------------
if(isset($_POST["io_mode"])){
    $exec = "/bin/sed -i 's/io_mode=.*/io_mode=\\\"".$_POST["io_mode"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["ap_mode"])){
    $exec = "/bin/sed -i 's/ap_mode=.*/ap_mode=\\\"".$_POST["ap_mode"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
    
    if($_POST["ap_mode"] == "2") {
        $exec = "/bin/sed -i 's/io_action=.*/io_action=\\\"at0\\\";/g' options_config.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=at0/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    } else {
        $exec = "/bin/sed -i 's/io_action=.*/io_action=\\\"$io_in_iface\\\";/g' options_config.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=$io_in_iface/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    }
}

if(isset($_POST["io_in_iface"])){
    $exec = "/bin/sed -i 's/io_in_iface=.*/io_in_iface=\\\"".$_POST["io_in_iface"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
	
    // replace interface in hostapd.conf and hostapd-secure.conf
    $exec = "/bin/sed -i 's/^interface=.*/interface=".$_POST["io_in_iface"]."/g' $mod_path/includes/conf/hostapd.conf";
    exec_fruitywifi($exec);
    $exec = "/bin/sed -i 's/^interface=.*/interface=".$_POST["io_in_iface"]."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
    
    $exec = "/bin/sed -i 's/interface=.*/interface=".$_POST["io_in_iface"]."/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
    
    //EXTRACT MACADDRESS
    $exec = "/sbin/ifconfig -a ".$_POST["io_in_iface"]." |grep HWaddr";
    //$output = exec("$bin_danger \"" . $exec . "\"" ); //DEPRECATED
    $output = exec_fruitywifi($exec);
    $output = preg_replace('/\s+/', ' ',$output);
    $output = explode(" ",$output);
    
    $exec = "/bin/sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd.conf";
    exec_fruitywifi($exec);
    $exec = "/bin/sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
    
    // IF AP_MODE IS AIRMON-NG KEEPS AT0 IN DNSMASQ    
    if($ap_mode == "2") {
        $exec = "/bin/sed -i 's/io_action=.*/io_action=\\\"at0\\\";/g' options_config.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=at0/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    } else {
        $exec = "/bin/sed -i 's/io_action=.*/io_action=\\\"".$_POST["io_in_iface"]."\\\";/g' options_config.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=".$_POST["io_in_iface"]."/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    }
}

if(isset($_POST["io_in_set"])){
    $exec = "/bin/sed -i 's/io_in_set=.*/io_in_set=\\\"".$_POST["io_in_set"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_in_ip"])){
    $exec = "/bin/sed -i 's/io_in_ip=.*/io_in_ip=\\\"".$_POST["io_in_ip"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
	
    // DNSMASQ (replace ip)
    $exec = "/bin/sed -i 's/server=.*/server=\/\#\/".$_POST["io_in_ip"]."/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
	
    $exec = "/bin/sed -i 's/listen-address=.*/listen-address=".$_POST["io_in_ip"]."/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
	
    $ip = explode(".",$_POST["io_in_ip"]);
    $sub = $ip[0] . "." . $ip[1] . "." . $ip[2];
    
    $exec = "/bin/sed -i 's/dhcp-range=.*/dhcp-range=".$sub.".50,".$sub.".100,12h/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_in_mask"])){
    $exec = "/bin/sed -i 's/io_in_mask=.*/io_in_mask=\\\"".$_POST["io_in_mask"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_in_gw"])){
    $exec = "/bin/sed -i 's/io_in_gw=.*/io_in_gw=\\\"".$_POST["io_in_gw"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_out_iface"])){
    $exec = "/bin/sed -i 's/io_out_iface=.*/io_out_iface=\\\"".$_POST["io_out_iface"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_out_set"])){
    $exec = "/bin/sed -i 's/io_out_set=.*/io_out_set=\\\"".$_POST["io_out_set"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_out_ip"])){
    $exec = "/bin/sed -i 's/io_out_ip=.*/io_out_ip=\\\"".$_POST["io_out_ip"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_out_mask"])){
    $exec = "/bin/sed -i 's/io_out_mask=.*/io_out_mask=\\\"".$_POST["io_out_mask"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["io_out_gw"])){
    $exec = "/bin/sed -i 's/io_out_gw=.*/io_out_gw=\\\"".$_POST["io_out_gw"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
}

// ------------ IN | OUT (END) -------------

// -------------- WIRELESS ------------------

if(isset($_POST['newSSID'])){
	
    $hostapd_ssid=$_POST['newSSID'];
    
    $exec = "sed -i 's/hostapd_ssid=.*/hostapd_ssid=\\\"".$_POST['newSSID']."\\\";/g' options_config.php";
    exec_fruitywifi($exec);

    $exec = "/usr/sbin/karma-hostapd_cli -p /var/run/hostapd-phy0 karma_change_ssid " . $_POST['newSSID'];
    exec_fruitywifi($exec);
    
    // replace interface in hostapd.conf and hostapd-secure.conf
    $exec = "/bin/sed -i 's/^ssid=.*/ssid=".$_POST["newSSID"]."/g' $mod_path/includes/conf/hostapd.conf";
    exec_fruitywifi($exec);
    $exec = "/bin/sed -i 's/^ssid=.*/ssid=".$_POST["newSSID"]."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
}


if (isset($_POST['hostapd_secure'])) {
    $exec = "sed -i 's/hostapd_secure=.*/hostapd_secure=\\\"".$_POST["hostapd_secure"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);

    $hostapd_secure = $_POST["hostapd_secure"];
}

if (isset($_POST['hostapd_wpa_passphrase'])) {
    $exec = "sed -i 's/hostapd_wpa_passphrase=.*/hostapd_wpa_passphrase=\\\"".$_POST["hostapd_wpa_passphrase"]."\\\";/g' options_config.php";
    exec_fruitywifi($exec);
    
    $exec = "sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$_POST["hostapd_wpa_passphrase"]."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
    
    $hostapd_wpa_passphrase = $_POST["hostapd_wpa_passphrase"];
}

header('Location: ../index.php?tab=3');
exit;

if ($type == "settings") {

    $exec = "/bin/sed -i 's/io_in_iface.*/io_in_iface = \\\"".$io_in_iface."\\\";/g' options_config.php";
    //$output = exec_fruitywifi($exec);

	$exec = "/bin/sed -i 's/io_in_ip.*/io_in_ip = \\\"".$io_in_ip."\\\";/g' options_config.php";
    //$output = exec_fruitywifi($exec);
	
    header('Location: ../index.php?tab=3');
    exit;

}

header('Location: ../index.php');

?>
