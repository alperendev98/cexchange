<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	require_once("inc/config.inc.php");

	if (!(isset($_SESSION['userid']) && is_numeric($_SESSION['userid'])))
	{
		// check cookie
		if (!CheckCookieLogin())
		{
			header("Location: login.php?msg=3");
			exit();
		}
	}
	else
	{
		$userid	= (int)$_SESSION['userid'];
	}

?>