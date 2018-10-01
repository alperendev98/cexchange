<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();

	unset($_SESSION['adm']['id'], $_SESSION['adm']['role'], $_SESSION['adm']['pages']);
	
	session_destroy();

	header("Location: login.php");
	exit();
	
?>