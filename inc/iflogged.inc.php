<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	if (isset($_SESSION['userid']) && is_numeric($_SESSION['userid']))
	{
		header("Location: myaccount.php");
		exit();
	}

?>