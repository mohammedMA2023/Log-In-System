<?php
session_start();
$auth_opt = "";
if ((!isset($_SESSION["status"])) || ($_SESSION["status"] != "loggedIn")){
    $auth_opt = '';
    echo <<<EOT



      EOT;
}
else{
    $auth_opt = '';
}
	echo '';
?>