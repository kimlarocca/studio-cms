<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_wotg = "localhost";
$database_wotg = "studiocm_cms";
$username_wotg = "studiocm_kim";
$password_wotg = "Lotus18641864!";
$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
?>