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

include "../../../config/config.php";
include "../_info_.php";
include "../../../login_check.php";
include "../../../functions.php";

//include "../_info_.php";

// Checking POST & GET variables...
if ($regex == 1) {
	regex_standard($_POST['type'], "../../../msg.php", $regex_extra);
}

$type = $_POST['type'];

/*
$mod_mgmt_io_in_iface = $_POST['mod_mgmt_io_in_iface'];
$mod_mgmt_io_in_ip = $_POST['mod_mgmt_io_in_ip'];
$mod_mgmt_hostapd_ssid = $_POST['mod_mgmt_hostapd_ssid'];
$mod_mgmt_hostapd_wpa_passphrase = $_POST['mod_mgmt_hostapd_wpa_passphrase'];
$mod_mgmt_hostapd_secure = $_POST['mod_mgmt_hostapd_secure'];
*/

$mod_mgmt_io_mode = $_POST["mod_mgmt_io_mode"];
$mod_mgmt_ap_mode = $_POST["mod_mgmt_ap_mode"];

$mod_mgmt_io_in_iface = $_POST["mod_mgmt_io_in_iface"];
$mod_mgmt_io_in_set = $_POST["mod_mgmt_io_in_set"];
$mod_mgmt_io_in_ip = $_POST["mod_mgmt_io_in_ip"];
$mod_mgmt_io_in_mask = $_POST["mod_mgmt_io_in_mask"];
$mod_mgmt_io_in_gw = $_POST["mod_mgmt_io_in_gw"];

$mod_mgmt_io_out_iface = $_POST["mod_mgmt_io_out_iface"];
$mod_mgmt_io_out_set = $_POST["mod_mgmt_io_out_set"];
$mod_mgmt_io_out_ip = $_POST["mod_mgmt_io_out_ip"];
$mod_mgmt_io_out_mask = $_POST["mod_mgmt_io_out_mask"];
$mod_mgmt_io_out_gw = $_POST["mod_mgmt_io_out_gw"];

$mod_mgmt_hostapd_secure = $_POST["mod_mgmt_hostapd_secure"];
$mod_mgmt_hostapd_ssid = $_POST["mod_mgmt_hostapd_ssid"];
$mod_mgmt_hostapd_wpa_passphrase = $_POST["mod_mgmt_hostapd_wpa_passphrase"];

// ------------ IN | OUT (START) -------------
if(isset($_POST["mod_mgmt_io_mode"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_mode=.*/mod_mgmt_io_mode=\\\"".$mod_mgmt_io_mode."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_ap_mode"])){
    $exec = "/bin/sed -i 's/mod_mgmt_ap_mode=.*/mod_mgmt_ap_mode=\\\"".$mod_mgmt_ap_mode."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
    
    if($mod_mgmt_ap_mode == "2") {
        $exec = "/bin/sed -i 's/mod_mgmt_io_action=.*/mod_mgmt_io_action=\\\"at0\\\";/g' ../_info_.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=at0/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    } else {
        $exec = "/bin/sed -i 's/mod_mgmt_io_action=.*/mod_mgmt_io_action=\\\"$mod_mgmt_io_in_iface\\\";/g' ../_info_.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=$mod_mgmt_io_in_iface/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    }
}

if(isset($_POST["mod_mgmt_io_in_iface"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_in_iface=.*/mod_mgmt_io_in_iface=\\\"".$mod_mgmt_io_in_iface."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
	
    // replace interface in hostapd.conf and hostapd-secure.conf
    $exec = "/bin/sed -i 's/^interface=.*/interface=".$mod_mgmt_io_in_iface."/g' $mod_path/includes/conf/hostapd.conf";
    exec_fruitywifi($exec);
    $exec = "/bin/sed -i 's/^interface=.*/interface=".$mod_mgmt_io_in_iface."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
    
    $exec = "/bin/sed -i 's/interface=.*/interface=".$mod_mgmt_io_in_iface."/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
    
    //EXTRACT MACADDRESS
    $exec = "/sbin/ifconfig -a ".$mod_mgmt_io_in_iface." |grep HWaddr";
    //$output = exec("$bin_danger \"" . $exec . "\"" ); //DEPRECATED
    $output = exec_fruitywifi($exec);
    $output = preg_replace('/\s+/', ' ',$output);
    $output = explode(" ",$output);
    
    $exec = "/bin/sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd.conf";
    exec_fruitywifi($exec);
    $exec = "/bin/sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
    
    // IF AP_MODE IS AIRMON-NG KEEPS AT0 IN DNSMASQ    
    if($mod_mgmt_ap_mode == "2") {
        $exec = "/bin/sed -i 's/mod_mgmt_io_action=.*/mod_mgmt_io_action=\\\"at0\\\";/g' ../_info_.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=at0/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    } else {
        $exec = "/bin/sed -i 's/mod_mgmt_io_action=.*/mod_mgmt_io_action=\\\"".$mod_mgmt_io_in_iface."\\\";/g' ../_info_.php";
		exec_fruitywifi($exec);
        
        $exec = "/bin/sed -i 's/interface=.*/interface=".$mod_mgmt_io_in_iface."/g' $mod_path/includes/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
    }
}

if(isset($_POST["mod_mgmt_io_in_set"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_in_set=.*/mod_mgmt_io_in_set=\\\"".$mod_mgmt_io_in_set."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_in_ip"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_in_ip=.*/mod_mgmt_io_in_ip=\\\"".$mod_mgmt_io_in_ip."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
	
    // DNSMASQ (replace ip)
    $exec = "/bin/sed -i 's/server=.*/server=\/\#\/".$mod_mgmt_io_in_ip."/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
	
    $exec = "/bin/sed -i 's/listen-address=.*/listen-address=".$mod_mgmt_io_in_ip."/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
	
    $ip = explode(".",$_POST["mod_mgmt_io_in_ip"]);
    $sub = $ip[0] . "." . $ip[1] . "." . $ip[2];
    
    $exec = "/bin/sed -i 's/dhcp-range=.*/dhcp-range=".$sub.".50,".$sub.".100,12h/g' $mod_path/includes/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_in_mask"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_in_mask=.*/mod_mgmt_io_in_mask=\\\"".$mod_mgmt_io_in_mask."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_in_gw"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_in_gw=.*/mod_mgmt_io_in_gw=\\\"".$mod_mgmt_io_in_gw."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_out_iface"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_out_iface=.*/mod_mgmt_io_out_iface=\\\"".$mod_mgmt_io_out_iface."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_out_set"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_out_set=.*/mod_mgmt_io_out_set=\\\"".$mod_mgmt_io_out_set."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_out_ip"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_out_ip=.*/mod_mgmt_io_out_ip=\\\"".$mod_mgmt_io_out_ip."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_out_mask"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_out_mask=.*/mod_mgmt_io_out_mask=\\\"".$mod_mgmt_io_out_mask."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

if(isset($_POST["mod_mgmt_io_out_gw"])){
    $exec = "/bin/sed -i 's/mod_mgmt_io_out_gw=.*/mod_mgmt_io_out_gw=\\\"".$mod_mgmt_io_out_gw."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
}

// ------------ IN | OUT (END) -------------

// -------------- WIRELESS ------------------

if(isset($_POST['newSSID'])){
	
    $hostapd_ssid=$_POST['newSSID'];
    
    $exec = "sed -i 's/mod_mgmt_hostapd_ssid=.*/mod_mgmt_hostapd_ssid=\\\"".$_POST['newSSID']."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);

    $exec = "/usr/sbin/karma-hostapd_cli -p /var/run/hostapd-phy0 karma_change_ssid " . $_POST['newSSID'];
    exec_fruitywifi($exec);
    
    // replace interface in hostapd.conf and hostapd-secure.conf
    $exec = "/bin/sed -i 's/^ssid=.*/ssid=".$_POST["newSSID"]."/g' $mod_path/includes/conf/hostapd.conf";
    exec_fruitywifi($exec);
    $exec = "/bin/sed -i 's/^ssid=.*/ssid=".$_POST["newSSID"]."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
}


if (isset($_POST['mod_mgmt_hostapd_secure'])) {
    $exec = "sed -i 's/mod_mgmt_hostapd_secure=.*/mod_mgmt_hostapd_secure=\\\"".$mod_mgmt_hostapd_secure."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);

    $mod_mgmt_hostapd_secure = $_POST["mod_mgmt_hostapd_secure"];
}

if (isset($_POST['mod_mgmt_hostapd_wpa_passphrase'])) {
    $exec = "sed -i 's/mod_mgmt_hostapd_wpa_passphrase=.*/mod_mgmt_hostapd_wpa_passphrase=\\\"".$mod_mgmt_hostapd_wpa_passphrase."\\\";/g' ../_info_.php";
    exec_fruitywifi($exec);
    
    $exec = "sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$mod_mgmt_hostapd_wpa_passphrase."/g' $mod_path/includes/conf/hostapd-secure.conf";
    exec_fruitywifi($exec);
    
    $mod_mgmt_hostapd_wpa_passphrase = $_POST["mod_mgmt_hostapd_wpa_passphrase"];
}

header('Location: ../index.php?tab=2');
exit;

if ($type == "settings") {

    $exec = "/bin/sed -i 's/mod_mgmt_io_in_iface.*/mod_mgmt_io_in_iface = \\\"".$mod_mgmt_io_in_iface."\\\";/g' ../_info_.php";
    //$output = exec_fruitywifi($exec);

	$exec = "/bin/sed -i 's/mod_mgmt_io_in_ip.*/mod_mgmt_io_in_ip = \\\"".$mod_mgmt_io_in_ip."\\\";/g' ../_info_.php";
    //$output = exec_fruitywifi($exec);
	
    header('Location: ../index.php?tab=2');
    exit;

}

header('Location: ../index.php');

?>
