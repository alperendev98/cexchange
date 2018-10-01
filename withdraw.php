<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");

	$query	= "SELECT * FROM exchangerix_users WHERE user_id='$userid' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);

	if (mysqli_num_rows($result) > 0)
	{
		$row = mysqli_fetch_array($result);
	}
	else
	{
		header ("Location: logout.php");
		exit();
	}


	$amount = DisplayMoney(MIN_PAYOUT_PER_TRANSACTION, $hide_currency = 1);
	if (isset($_POST['amount']) && is_numeric($_POST['amount']))
	{
		$amount = mysqli_real_escape_string($conn, getPostParameter('amount'));
	}

	
	// password verification
	if (isset($_POST['action']) && $_POST['action'] == "check_password")
	{
		unset($_SESSION['password_verified']);
		unset($errs);
		$errs = array();

		$pwd = mysqli_real_escape_string($conn, getPostParameter('password'));

		if (!($pwd))
		{
			$errs[] = "Please enter password";
		}
		else
		{
			if (PasswordEncryption($pwd) !== $row['password'])
			{
				$errs[] = "Wrong password";
			}
		}

		if (count($errs) == 0)
		{
			// setup verification for one hour
			$_SESSION['password_verified'] = time() + (1*1*60*60);
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}
	}


	if (isset($_POST['withdraw']) && $_POST['withdraw'] != "")
	{
		unset($errs);
		$errs = array();

		$amount				= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$payment_method		= (int)getPostParameter('payment_method');
		$payment_details	= mysqli_real_escape_string($conn, nl2br(getPostParameter('payment_details')));
		$current_balance	= GetUserBalance($userid, 1);

		if (!(is_numeric($amount) && $amount > 0))
		{
			$errs[] = CBE1_WITHDRAW_ERR;
			$amount = "";
		}
		elseif (!(isset($payment_method) && $payment_method != 0))
		{
			$errs[] = CBE1_WITHDRAW_ERR2;
		}
		elseif (!(isset($payment_details) && $payment_details != ""))
		{
			$errs[] = CBE1_WITHDRAW_ERR3;
		}
		else
		{
			if ($amount < MIN_PAYOUT_PER_TRANSACTION)
			{
				$errs[] = CBE1_WITHDRAW_ERR4." ".DisplayMoney(MIN_PAYOUT_PER_TRANSACTION);
			}

			if ($amount > $current_balance)
			{
				$errs[] = CBE1_WITHDRAW_ERR5;
			}

			if ($current_balance < MIN_PAYOUT)
			{
				$errs[] = CBE1_WITHDRAW_ERR6." ".DisplayMoney(MIN_PAYOUT);
			}

			$presult = smart_mysql_query("SELECT * FROM exchangerix_pmethods WHERE pmethod_id='$payment_method' AND status='active' LIMIT 1");
			if (mysqli_num_rows($presult) == 0)
			{
				$errs[] = CBE1_WITHDRAW_ERR7;
			}
			else
			{
				$prow = mysqli_fetch_array($presult);
				$commission = $prow['commission'];
			}
		}

		if (count($errs) == 0)
		{
			if ($commission != "" && $commission != "0.00")
			{
				if (strstr($commission, '%'))
				{
					$commission_percent = str_replace('%','',$commission);
					$transaction_commission = CalculatePercentage($amount, $commission_percent);
				}
				else
				{
					$transaction_commission = $commission;
				}
			}

			$reference_id = GenerateReferenceID();
			$rp_query = "INSERT INTO exchangerix_transactions SET reference_id='$reference_id', user_id='$userid', payment_type='Withdrawal', payment_method='$payment_method', payment_details='$payment_details', transaction_commision='$transaction_commission', amount='$amount', status='request', created=NOW()";
		
			if (smart_mysql_query($rp_query))
			{
				header("Location: withdraw.php?msg=sent");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_WITHDRAW_TITLE;

	require_once ("inc/header.inc.php");

?>

	<div class="row">
		<div class="col-md-12 hidden-xs">
		<div id="acc_user_menu">
			<ul><?php require("inc/usermenu.inc.php"); ?></ul>
		</div>
	</div>

	<h1><img src="<?php echo SITE_URL; ?>images/money.png" align="absmiddle" /> <?php echo CBE1_WITHDRAW_TITLE; ?></h1>


	<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
				switch ($_GET['msg'])
				{
					case "sent": echo CBE1_WITHDRAW_SENT; break;
				}
			?>
		</div>
	<?php }else{ ?>


		<?php if (isset($allerrors)) { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>


		<?php if (GetUserBalance($userid, 1) >= MIN_PAYOUT) { ?>
		
			<?php if (isset($_SESSION['password_verified']) && $_SESSION['password_verified'] > time()) { ?>
	
				<?php if (!(GetUserBalance($userid, 1) > 0)) { ?>
					<p align="center"><?php echo CBE1_WITHDRAW_MSG; ?></p>
					<p align="center"><?php echo CBE1_WITHDRAW_BALANCE2; ?>: <b><?php echo DisplayMoney($row['balance']); ?></b>. <?php echo CBE1_WITHDRAW_MSG2; ?> <b><?php echo DisplayMoney(MIN_PAYOUT); ?></b>.</p>
				<?php } ?>

				<form action="" method="post">
				<table width="100%" bgcolor="#F9F9F9" align="center" cellpadding="3" cellspacing="0" border="0">
				<tr>
					<td height="30" colspan="2" align="center" valign="middle">
						<b><?php echo CBE1_WITHDRAW_TITLE; ?></b>
						<br/><div class="sline"></div>
					</td>
				</tr>
				<tr>
					<td width="40%" align="right" nowrap="nowrap"><?php echo CBE1_WITHDRAW_AMOUNT; ?>:</td>
					<td align="left" valign="middle">
						<?php echo SITE_CURRENCY; ?><input type="text" class="form-control" name="amount" value="<?php echo @$amount; ?>" size="7" />
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo CBE1_WITHDRAW_PMETHOD; ?>:</td>
					<td align="left" valign="middle">
						<select name="payment_method" onchange="this.form.submit();">
							<option value=""><?php echo CBE1_WITHDRAW_PMETHOD_SELECT; ?></option>
							<?php

								$sql_pmethods = smart_mysql_query("SELECT * FROM exchangerix_pmethods WHERE status='active' ORDER BY pmethod_title ASC");
							
								while ($row_pmethods = mysqli_fetch_array($sql_pmethods))
								{
									if ($payment_method == $row_pmethods['pmethod_id'] || $_POST['payment_method'] == $row_pmethods['pmethod_id']) $selected = " selected=\"selected\""; else $selected = "";
									echo "<option value=\"".$row_pmethods['pmethod_id']."\"".$selected.">".$row_pmethods['pmethod_title']."</option>";
								}
							?>
						</select>
					</td>
				</tr>
				<?php if (isset($_POST['payment_method']) && is_numeric($_POST['payment_method'])) { ?>
				<?php 
						$payment_method_id = (int)$_POST['payment_method'];
						$pquery = "SELECT * FROM exchangerix_pmethods WHERE pmethod_id='$payment_method_id' AND status='active' LIMIT 1";
						$prow = mysqli_fetch_array(smart_mysql_query($pquery));			
				?>
					<?php if ($prow['commission'] != "0.00" && $prow['commission'] != "") { ?>
					<tr>
						<td align="right" valign="bottom" nowrap="nowrap"><?php echo CBE1_WITHDRAW_FEE; ?>:</td>
						<td align="left" valign="middle"><b><?php echo DisplayMoney($prow['commission']); ?></b></td>
					</tr>
					<?php } ?>
				<tr>
					<td align="right" valign="bottom" nowrap="nowrap"><?php echo CBE1_WITHDRAW_DETAILS; ?>:<br/><br/><br/><br/></td>
					<td align="left" valign="middle">
						<?php echo $prow['pmethod_details']; ?><br/>
						<textarea name="payment_details" cols="40" rows="4" class="form-control"><?php echo getPostParameter('payment_details'); ?></textarea>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td>&nbsp;</td>
					<td height="40" align="left" valign="top">
						<input type="hidden" name="action" value="withdraw" />
						<input type="submit" class="submit" name="withdraw" value="<?php echo CBE1_WITHDRAW_BUTTON; ?>" />
					</td>
				</tr>
				</table>
				</form>

			<?php }else{ ?>
			
					<?php if (!(isset($_POST['action']) && $_POST['action'] == "check_password")) { ?>
						<p align="center"><?php echo CBE1_WITHDRAW_PASSWORD; ?></p>
					<?php } ?>

					<div class="form_box">
					<form action="" method="post">
					<table width="100%" align="center" cellpadding="3" cellspacing="0" border="0">
					<tr height="50">
						<td width="30%" align="right" valign="middle"><?php echo CBE1_LABEL_PWD; ?>:</td>
						<td width="30%" align="left" valign="middle"><input type="password" class="form-control" name="password" value="" size="40" required="required" /></td>
						<td width="30%" align="left" valign="middle">
							<input type="hidden" name="action" value="check_password" />
							<input type="submit" class="submit" name="submit" id="submit" value="<?php echo CBE1_SUBMIT_BUTTON; ?>" />
						</td>
					</tr>
					</table>
					</form>
					</div>
			
			<?php } ?>

		<?php }else{ ?>
			<div class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> <?php echo CBE1_WITHDRAW_MSG3; ?> <b><?php echo DisplayMoney(MIN_PAYOUT); ?></b> <?php echo CBE1_WITHDRAW_MSG4; ?></div>
		<?php } ?>

	<?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>