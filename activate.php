<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/iflogged.inc.php");
	require_once("inc/config.inc.php");


	if (isset($_GET['key']) && is_string($_GET['key']) && preg_match('/^[a-z\d]{32}$/i', $_GET['key']))
	{
		$activation_key = strtolower(mysqli_real_escape_string($conn, getGetParameter('key')));
		$activation_key = preg_replace("/[^0-9a-zA-Z]/", " ", $activation_key);
		$activation_key = substr(trim($activation_key), 0, 32);

		// activate user
		$check_result = smart_mysql_query("SELECT status FROM exchangerix_users WHERE activation_key='$activation_key' LIMIT 1");
        if (mysqli_num_rows($check_result) > 0)
		{
			$check_row = mysqli_fetch_array($check_result);

			if ($check_row['status'] == "active")
			{
				header ("Location: activate.php?msg=3");
				exit();
			}
			elseif ($check_row['status'] == "inactive")
			{
				smart_mysql_query("UPDATE exchangerix_users SET status='active', activation_key='' WHERE activation_key='$activation_key' AND login_count='0' LIMIT 1");

				header ("Location: activate.php?msg=2");
				exit();
			}
		}
		else
		{
			header ("Location: index.php");
			exit();
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_ACTIVATION_TITLE;

	require_once ("inc/header.inc.php");

?>

	<?php if (isset($_GET['msg']) && is_numeric($_GET['msg'])) { ?>
		<?php if ($_GET['msg'] == 1) { ?>
			<h1><?php echo CBE1_ACTIVATION_MSG1; ?></h1>
			<p><?php echo CBE1_ACTIVATION_TEXT01; ?></p>
			<p><?php echo CBE1_ACTIVATION_TEXT02; ?></p>
		<?php } ?>
		<?php if ($_GET['msg'] == 2) { ?>
			<h1><?php echo CBE1_ACTIVATION_MSG2; ?></h1>
			<p><?php echo CBE1_ACTIVATION_TEXT2; ?></p>
		<?php } ?>
		<?php if ($_GET['msg'] == 3) { ?>
			<h1><?php echo CBE1_ACTIVATION_MSG3; ?></h1>
			<p><?php echo CBE1_ACTIVATION_TEXT3; ?></p>
		<?php } ?>
	<?php } ?>

<?php require_once("inc/footer.inc.php"); ?>