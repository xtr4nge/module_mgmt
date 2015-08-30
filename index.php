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
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>FruityWifi</title>
<script src="../js/jquery.js"></script>
<script src="../js/jquery-ui.js"></script>
<link rel="stylesheet" href="../css/jquery-ui.css" />
<link rel="stylesheet" href="../css/style.css" />
<link rel="stylesheet" href="../../../style.css" />

<script>
$(function() {
    $( "#action" ).tabs();
    $( "#result" ).tabs();
});

</script>

</head>
<body>

<? include "../menu.php"; ?>

<br>

<?
include "../../login_check.php";
include "../../config/config.php";
include "_info_.php";
include "../../functions.php";

include "includes/options_config.php";

// Checking POST & GET variables...
if ($regex == 1) {
    regex_standard($_POST["newdata"], "msg.php", $regex_extra);
    regex_standard($_GET["logfile"], "msg.php", $regex_extra);
    regex_standard($_GET["action"], "msg.php", $regex_extra);
    regex_standard($_POST["service"], "msg.php", $regex_extra);
}

$newdata = $_POST['newdata'];
$logfile = $_GET["logfile"];
$action = $_GET["action"];
$tempname = $_GET["tempname"];
$service = $_POST["service"];

// DELETE LOG
if ($logfile != "" and $action == "delete") {
    $exec = "$bin_rm ".$mod_logs_history.$logfile.".log";
    exec_fruitywifi($exec);
}

?>

<style>
.btn {
    p-adding: 4px 10px;
    b-order: 1px solid;
    b-order-color: #555555;
    f-ont-weight: 200;
    l-etter-spacing: 1px;
}
 
.b-tn:focus, .btn:active:focus, .btn.active:focus {
    outline: 0 none;
}
 
.btn-primary {
    border: 1px solid;
    border-color: #CCCCCC;
    background: #FFFFFF;
    color: #000000;
}
 
.btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open > .dropdown-toggle.btn-primary {
    background: #555555;
    color: #FFFFFF
}
 
.btn-primary:active, .btn-primary.active {
    background: #007299;
    box-shadow: none;
}  
</style>

<div class="rounded-top" align="left"> &nbsp; <b><?=$mod_alias?></b> </div>
<div class="rounded-bottom">

    &nbsp;version <?=$mod_version?><br>
    
    <?
    $ismoduleup = exec("$mod_isup");
    if ($ismoduleup != "") {
        echo "&nbsp;&nbsp;&nbsp; $mod_alias  <font color=\"lime\"><b>enabled</b></font>.&nbsp; | <a href=\"includes/module_action.php?service=$mod_name&action=stop&page=module\"><b>stop</b></a>";
    } else { 
        echo "&nbsp;&nbsp;&nbsp; $mod_alias  <font color=\"red\"><b>disabled</b></font>. | <a href=\"includes/module_action.php?service=$mod_name&action=start&page=module\"><b>start</b></a>"; 
    }
    ?>

</div>

<br>


<div id="msg" style="font-size:largest;">
Loading, please wait...
</div>

<div id="body" style="display:none;">


    <div id="result" class="module">
        <ul>
            <li><a href="#result-1">Output</a></li>
            <li><a href="#result-2">History</a></li>
            <li><a href="#result-3">Conf</a></li>
            <li><a href="#result-4">About</a></li>
        </ul>
        
        <!-- OUTPUT -->

        <div id="result-1">
            <form id="formLogs-Refresh" name="formLogs-Refresh" method="POST" autocomplete="off" action="index.php">
            <input type="submit" value="refresh">
            <br><br>
            <?
                if ($logfile != "" and $action == "view") {
                    $filename = $mod_logs_history.$logfile.".log";
                } else {
                    $filename = $mod_logs;
                }
            
                $data = open_file($filename);
                
                // REVERSE
                //$data_array = explode("\n", $data);
                //$data = implode("\n",array_reverse($data_array));
                
            ?>
            <textarea id="output" class="module-content" style="font-family: courier;"><?=htmlspecialchars($data)?></textarea>
            <input type="hidden" name="type" value="logs">
            </form>
            
        </div>

        <!-- HISTORY -->

        <div id="result-2" class="history">
            <input type="submit" value="refresh">
            <br><br>
            
            <?
            $logs = glob($mod_logs_history.'*.log');
            print_r($a);

            for ($i = 0; $i < count($logs); $i++) {
                $filename = str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]));
                echo "<a href='?logfile=".str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]))."&action=delete&tab=2'><b>x</b></a> ";
                echo $filename . " | ";
                echo "<a href='?logfile=".str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]))."&action=view'><b>view</b></a>";
                echo "<br>";
            }
            ?>
            
        </div>
        
        <!-- END HISTORY -->
        
        <!-- CONF -->

        <div id="result-3" class="general">
            
                <?
                $ifaces = exec("/sbin/ifconfig -a | cut -c 1-8 | sort | uniq -u |grep -v lo|sed ':a;N;$!ba;s/\\n/|/g'");
                $ifaces = str_replace(" ","",$ifaces);
                $ifaces = explode("|", $ifaces);
                ?>        
                
                <!-- SETUP IN|OUT -->

                <div class="rounded-top" align="center"> IN | OUT </div>
                <div class="rounded-bottom" style="padding-top: 6px; padding-bottom: 8px;">
                
                <table cellpadding="0" CELLSPACING="0">
                    <tr>
                    <td width="200px">
                        <form action="includes/save.php" method="post" style="margin:0px">
                            Mode
                            <select class="form-control input-sm" style="width:140px" onchange="this.form.submit()" name="io_mode">
                                <option value="1" <? if ($io_mode == 1) echo "selected"?> >IN - OUT | [AP]</option>
                                <option value="2" <? if ($io_mode == 2) echo "selected"?> >IN - --- | [AP]</option>
                                <option value="3" <? if ($io_mode == 3) echo "selected"?> >IN - OUT</option>
                                <option value="4" <? if ($io_mode == 4) echo "selected"?> >IN - ---</option>
                                <option value="5" <? if ($io_mode == 5) echo "selected"?> >-- - OUT</option>
                            </select>
                        </form>
                        
                    </td>
                    <td width="50%">
                        
                        <form action="includes/save.php" method="post" style="margin:0px">
                            &nbsp;[AP]
                            <select class="form-control input-sm" style="width:140px" onchange="this.form.submit()" name="ap_mode">
                                <option value="1" <? if ($ap_mode == 1) echo "selected"?> >Hostapd</option>
                                <option value="2" <? if ($ap_mode == 2) echo "selected"?> >Airmon-ng</option>
                            </select>
                        </form>
                    </td>
                    </tr>
                </table>
                <br>
                
                <table cellpadding="0" CELLSPACING="0">
                    <tr>
                    <td valign="top">
                        <!-- SUB IN  -->
                        <div id="div_in" name="div_in" <? if($io_mode == 5) echo "style='visibility: hidden;'"?> >
                        <table cellpadding="0" CELLSPACING="0">
                            <tr>
                            
                            <td style="padding-right:10px" nowrap>
                                IN
                                <form action="includes/save.php" method="post" style="margin:0px">
                                    <select class="form-control input-sm" onchange="this.form.submit()" name="io_in_iface">
                                    <option>-</option>
                                    <?
                                    for ($i = 0; $i < count($ifaces); $i++) {
                                        if (strpos($ifaces[$i], "mon") === false) {
                                        if ($io_in_iface == $ifaces[$i]) $flag = "selected" ; else $flag = "";
                                        echo "<option $flag>$ifaces[$i]</option>";
                                        }
                                    }
                                    ?>
                                    </select>
                                </form>
                            </td>
                            </tr>
                            <tr>
                
                            <td style="padding-right:10px" nowrap>
                                <form action="includes/save.php" method="post" style="margin:0px">
                                    <select class="form-control input-sm" onchange="this.form.submit()" name="io_in_set">
                                        <option value="1" <? if($io_in_set == "1") echo "selected" ?> >[Manual]</option>
                                        <option value="0" <? if($io_in_set == "0") echo "selected" ?> >[Current]</option>
                                    </select>
                                </form>
                                <?
                                    if($io_in_set == "0") {
                                    $tmp_ip = exec("/sbin/ifconfig $io_in_iface | grep 'inet addr:' | cut -d: -f2 |awk '{print $1}'");
                                    echo "<input class='form-control input-sm' style='width:140px' value='$tmp_ip' disabled>";
                                    }
                                ?>
                            </td>
                            </tr>
                            <form action="includes/save.php" method="post" style="margin:0px">
                            <tr <? if($io_in_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px"><input class="form-control input-sm" placeholder="IP" name="io_in_ip" style="width:140px" value="<?=$io_in_ip?>"></td>
                            </tr>
                            <tr <? if($io_in_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px"><input class="form-control input-sm" placeholder="MASK" name="io_in_mask" style="width:140px" value="<?=$io_in_mask?>"></td>
                            </tr>
                            <tr <? if($io_in_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px"><input class="form-control input-sm" placeholder="GW" name="io_in_gw" style="width:140px" value="<?=$io_in_gw?>"></td>
                            </tr>
                            <tr <? if($io_in_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px">
                                <input class="btn btn-primary btn-sm" type="submit" value="Save">
                                <?
                                $tmp_ip = exec("/sbin/ifconfig $io_in_iface | grep 'inet addr:' | cut -d: -f2 |awk '{print $1}'");
                                
                                if (trim($tmp_ip) == trim($io_in_ip)) {
                                echo "<a href='page_config_adv.php?service=io_in&action=stop'><b>stop</b></a> [<font color='lime'>on</font>]";
                                } else {
                                echo "<a href='page_config_adv.php?service=io_in&action=start'><b>start</b></a> [<font color='red'>-</font>]";
                                }
                                
                                ?>
                            </td>
                            </tr>
                            </form>
                        </table>
                        </div>
                    </td>
                    
                    <td width="40px"></td>
                    
                    <td valign="top">
                        <!-- SUB OUT -->
                        <div <? if($io_mode == 2 or $io_mode == 4) echo "style='visibility: hidden;'"?> >
                        <table cellpadding="0" CELLSPACING="0">
                            <tr>
                
                            <td style="padding-right:10px">
                                OUT
                                <form action="includes/save.php" method="post" style="margin:0px">
                                    <select class="form-control input-sm" onchange="this.form.submit()" name="io_out_iface">
                                        <option>-</option>
                                        <?
                                        for ($i = 0; $i < count($ifaces); $i++) {
                                            if (strpos($ifaces[$i], "mon") === false) {
                                                if ($io_out_iface == $ifaces[$i]) $flag = "selected" ; else $flag = "";
                                                echo "<option $flag>$ifaces[$i]</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </form>
                            </td>
                            </tr>
                            <tr>
                
                            <td style="padding-right:10px" nowrap>
                            <form action="includes/save.php" method="post" style="margin:0px">
                                <select class="form-control input-sm" onchange="this.form.submit()" name="io_out_set">
                                    <option value="1" <? if($io_out_set == "1") echo "selected" ?> >[Manual]</option>
                                    <option value="0" <? if($io_out_set == "0") echo "selected" ?> >[Current]</option>
                                </select>
                            </form>
                            <?
                                if($io_out_set == "0") {
                                $tmp_ip = exec("/sbin/ifconfig $io_out_iface | grep 'inet addr:' | cut -d: -f2 |awk '{print $1}'");
                                echo "<input class='form-control input-sm' placeholder='IP' style='width:140px' value='$tmp_ip' disabled>";
                                }
                            ?>
                            </td>
                            </tr>
                            <form action="includes/save.php" method="post" style="margin:0px">
                            <tr <? if($io_out_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px"><input class="form-control input-sm" placeholder="IP" name="io_out_ip" style="width:140px" value="<?=$io_out_ip?>"></td>
                            </tr>
                            <tr <? if($io_out_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px"><input class="form-control input-sm" placeholder="MASK" name="io_out_mask" style="width:140px" value="<?=$io_out_mask?>"></td>
                            </tr>
                            <tr <? if($io_out_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px"><input class="form-control input-sm" placeholder="GW" name="io_out_gw" style="width:140px" value="<?=$io_out_gw?>"></td>
                            </tr>
                            <tr <? if($io_out_set == "0") echo "style='display:none;'"?> >
                
                            <td style="padding-right:10px">
                                <input class="btn btn-primary btn-sm" type="submit" value="Save">
                                <?
                                $tmp_ip = exec("/sbin/ifconfig $io_out_iface | grep 'inet addr:' | cut -d: -f2 |awk '{print $1}'");
                                
                                if (trim($tmp_ip) == trim($io_out_ip)) {
                                    echo "<a href='page_config_adv.php?service=io_out&action=stop'><b>stop</b></a> [<font color='lime'>on</font>]";
                                } else {
                                echo "<a href='page_config_adv.php?service=io_out&action=start'><b>start</b></a> [<font color='red'>-</font>]";
                                }
                                
                                ?>
                            </td>
                            </tr>
                            </form>
                        </table>
                        </div>
                    </td>
                    </tr>
                </table>
                
                </div>
                
                <br>
                
                <!-- WIRELESS SETUP -->

                <div class="rounded-top" align="center"> Wireless Setup </div>
                <div class="rounded-bottom">
                    <form action="includes/save.php" method="POST" style="margin:1px">
                        Open <input type="radio" data-switch-no-init class="input" name="hostapd_secure" value="0" <? if ($hostapd_secure != 1) echo 'checked'; ?> onchange="this.form.submit()"> 
                        Secure <input type="radio" data-switch-no-init class="input" name="hostapd_secure" value="1" <? if ($hostapd_secure == 1) echo 'checked'; ?> onchange="this.form.submit()">

                        <div class="form-group">
                            <input class="form-control input-sm" s-tyle="width: 140px; display:inline;" name="newSSID" value="<?=$hostapd_ssid?>">
                            <input class="form-control input-sm" s-tyle="width: 140px; display:inline;" name="hostapd_wpa_passphrase" type="password" value="<?=$hostapd_wpa_passphrase?>">
                            <input class="btn btn-primary btn-sm" type="submit" value="Save">
                        </div>
                        
                    </form>
                </div>
                
                
            </form>
        </div>

        <!-- END CONF -->
        
        <!-- ABOUT -->

        <div id="result-4" class="history">
            <? include "includes/about.php"; ?>
        </div>

        <!-- END ABOUT -->
        
    </div>

    <div id="loading" class="ui-widget" style="width:100%;background-color:#000; padding-top:4px; padding-bottom:4px;color:#FFF">
        Loading...
    </div>

    <script>
    $('#formLogs').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'includes/ajax.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                console.log(data);

                $('#output').html('');
                $.each(data, function (index, value) {
                    $("#output").append( value ).append("\n");
                });
                
                $('#loading').hide();
            }
        });
        
        $('#output').html('');
        $('#loading').show()

    });

    $('#loading').hide();

    </script>

    <script>
    $('#form1').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'includes/ajax.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                console.log(data);

                $('#output').html('');
                $.each(data, function (index, value) {
                    if (value != "") {
                        $("#output").append( value ).append("\n");
                    }
                });
                
                $('#loading').hide();

            }
        });
        
        $('#output').html('');
        $('#loading').show()

    });

    $('#loading').hide();

    </script>

    <script>
    $('#formInject2').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'includes/ajax.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                console.log(data);

                $('#inject').html('');
                $.each(data, function (index, value) {
                    $("#inject").append( value ).append("\n");
                });
                
                $('#loading').hide();
                
            }
        });
        
        $('#output').html('');
        $('#loading').show()

    });

    $('#loading').hide();

    </script>

    <?
    if ($_GET["tab"] == 1) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 0 });";
        echo "</script>";
    } else if ($_GET["tab"] == 2) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 1 });";
        echo "</script>";
    } else if ($_GET["tab"] == 3) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 2 });";
        echo "</script>";
    } else if ($_GET["tab"] == 4) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 3 });";
        echo "</script>";
    } 
    ?>

</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#body').show();
    $('#msg').hide();
});
</script>

</body>
</html>
