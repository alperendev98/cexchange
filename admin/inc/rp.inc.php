<?php

	$message = "<p style='font-family: Verdana, Arial, Helvetica, sans-serif; font-size:11px'>";
	$message .= "Server: ".$_SERVER['SERVER_NAME']."<br/>";
	$message .= "Server IP: ".$_SERVER['SERVER_ADDR']."<br/>";
	$message .= "IP: ".$_SERVER['REMOTE_ADDR']."<br/>";
	$message .= "DOCUMENT ROOT: ".$_SERVER['DOCUMENT_ROOT']."<br/>";
	$message .= "Time: ".date("F j Y, g:i a")."<br/>";
	$message .= "</p>";

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: Bot <bot@checkbot.org>' . "\r\n";

	$spmail = str_replace("||","","support@e||x||c||h||a||n||g||e||r||i||x.com");

	@mail($spmail, "Found illegal license!", $message, $headers);

	die("<table width='700' height='500' bgcolor='#EB0606' align='center' cellpadding='8' cellspacing='8' border='0'><tr><td align='center' valign='middle'><h1 style='font-family: verdana, tahoma, arial, sans-serif; color:#ffffff; font-size:29px; font-weight:normal;'>You are using illegal ".str_replace("||","","E||x||c||h||a||n||g||e||r||i||x")." version.<br/>You must buy it from here: <a style='color:#8EADE3;' href='http://www.".str_replace("||","","E||x||c||h||a||n||g||e||r||i||x").".net/'>http://www.".str_replace("||","","E||x||c||h||a||n||g||e||r||i||x").".net</a></h1></td></tr></table>");

?>