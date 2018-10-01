<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	
	unset($_SESSION['userid'], $_SESSION['FirstName'], $_SESSION['goto'], $_SESSION['goto_created'], $_SESSION['password_verified']);
	
	session_destroy();

	setcookie("usname", "", time()-3600);

	header("Location: login.php");
	exit();
	
?>