<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/adm_auth.inc.php");
	require_once("../inc/config.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	if (isset($_GET['type']) && $_GET['type'] == "cashback") $cpage = 7; else $cpage = 9;

	CheckAdminPermissions($cpage);

	if (isset($_POST["action"]) && $_POST["action"] == "edit_payment")
	{
		unset($errors);
		$errors = array();

		$transaction_id	= (int)getPostParameter('tid');
		$order_total	= mysqli_real_escape_string($conn, getPostParameter('order_total'));
		$amount			= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));
		$reason			= mysqli_real_escape_string($conn, nl2br(getPostParameter('reason')));
		$notification	= (int)getPostParameter('notification');

		if (!$status)
		{
			$errs[] = "Please select payment status";
		}
		else
		{
			if (!is_numeric($amount)) //&& $amount > 0
			{
				$errs[] = "Please enter correct amount";
				$amount = "";
			}

			if ($order_total)
			{
				if (!(is_numeric($order_total) && $order_total > 0))
					$errs[] = "Please enter correct order total amount";
				else
					$add_sql = "transaction_amount='$order_total',";
			}

			switch ($status)
			{
				case "confirmed":	$status="confirmed"; break;
				case "pending":		$status="pending"; break;
				case "declined":	$status="declined"; break;
				default:			$status="unknown"; break;
			}
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE exchangerix_transactions SET ".$add_sql." amount='$amount', status='$status', reason='$reason', updated=NOW() WHERE transaction_id='$transaction_id' LIMIT 1";
			$result = smart_mysql_query($sql);

				if ($notification == 1)
				{
					$tsql = "SELECT * FROM exchangerix_transactions WHERE transaction_id='$transaction_id' LIMIT 1";
					$tresult = smart_mysql_query($tsql);
					$ttotal = mysqli_num_rows($tresult);

					if ($ttotal > 0)
					{
						$trow = mysqli_fetch_array($tresult);
					}

					// send email ///////////////////////////////////////////////////////////////
					// if (urow['newsletter'] == 1) //
					$etemplate = GetEmailTemplate('manual_credit');
					$esubject = $etemplate['email_subject'];
					$emessage = $etemplate['email_message'];

					$emessage = str_replace("{transaction_id}", $reference_id, $emessage);
					$emessage = str_replace("{first_name}", GetUsername($trow['user_id'], $type = 3), $emessage);
					$emessage = str_replace("{payment_type}", $trow['payment_type'], $emessage);
					$emessage = str_replace("{amount}", DisplayMoney($amount), $emessage);
					$emessage = str_replace("{status}", $status, $emessage);
					$emessage = str_replace("{reason}", $reason, $emessage);//dev
					$to_email = $urow['fname'].' '.$urow['lname'].' <'.$urow['email'].'>';

					SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
					//////////////////////////////////////////////////////////////////////////////
				}

			if (isset($_GET['type']) && $_GET['type'] == "cashback") $go_to = "cashback.php?msg=updated"; else $go_to = "payments.php?msg=updated";
			
			header("Location: ".$go_to);
			exit();
		}
		else
		{
			$errormsg = "";
			foreach ($errs as $errorname)
				$errormsg .= $errorname."<br/>";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id'])) { $id = (int)$_GET['id']; } elseif (isset($_POST['tid']) && is_numeric($_POST['tid'])) { $id = (int)$_POST['tid']; }
	if (isset($id) && is_integer($id))
	{
		$query = "SELECT t.*, DATE_FORMAT(t.created, '".DATE_FORMAT." %h:%i %p') AS payment_date, u.username, u.fname, u.lname FROM exchangerix_transactions t, exchangerix_users u WHERE t.user_id=u.user_id AND t.transaction_id='$id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Edit Payment";
	require_once ("inc/header.inc.php");

?>
    
		<h2>Edit Payment</h2>

		<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<?php if (isset($errormsg)) { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>

			<form action="" method="post" name="form1">
            <table width="100%" style="background:#F9F9F9; border-radius: 7px;" cellpadding="3" cellspacing="3" border="0" align="center">
              <!--
			  <tr>
                <td width="14%" valign="middle" align="left" class="tb1">Payment ID:</td>
                <td valign="middle"><?php echo $row['transaction_id']; ?></td>
              </tr>
			  -->
              <tr>
                <td width="14%"  valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="middle"><?php echo $row['reference_id']; ?></td>
              </tr>
              <tr>
                <td  valign="middle" align="left" class="tb1">Member:</td>
                <td valign="middle"><a href="user_details.php?id=<?php echo $row['user_id']; ?>" class="user"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Username:</td>
                <td valign="middle"><?php echo $row['username']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Type:</td>
                <td valign="middle"><b>
					<?php
							switch ($row['payment_type'])
							{
								case "cashback":			echo PAYMENT_TYPE_CASHBACK; break;
								case "withdrawal":			echo PAYMENT_TYPE_WITHDRAWAL; break;
								case "referral_commission": echo PAYMENT_TYPE_RCOMMISSION; break;
								case "friend_bonus":		echo PAYMENT_TYPE_FBONUS; break;
								case "signup_bonus":		echo PAYMENT_TYPE_SBONUS; break;
								default:					echo $row['payment_type']; break;
							}
					?></b>
					<?php if ($row['ref_id'] > 0) { ?> | <a href="user_details.php?id=<?php echo $row['ref_id']; ?>" class="user"><?php echo GetUsername($row['ref_id']); ?></a><?php } ?>
				</td>
              </tr>
			  <?php if ($row['payment_type'] == "withdrawal" && $row['payment_method'] > 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Method:</td>
                <td valign="middle">
					<?php /*if ($row['payment_method'] == 1) { ?><img src="images/paypal.png" align="absmiddle" />&nbsp;<?php }*/ ?>
					<?php echo GetPaymentMethodByID($row['payment_method']); ?>
                </td>
              </tr>
			  <?php } ?>
			  <?php if ($row['payment_type'] == "cashback") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Order Amount:</td>
                <td valign="middle"><?php echo (SITE_CURRENCY_FORMAT <= 3) ? SITE_CURRENCY : ""; ?><input type="text" class="form-control" name="order_total" value="<?php echo ($row['transaction_amount'] != "0.0000") ? DisplayMoney($row['transaction_amount'], 1) : ""; ?>" size="6" /> <?php echo (SITE_CURRENCY_FORMAT > 3) ? SITE_CURRENCY : ""; ?></td>
              </tr>
			  <?php } ?>
			  <?php if ($row['payment_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Details:</td>
                <td valign="middle"><?php echo $row['payment_details']; ?></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1"><?php echo ($row['payment_type'] == "cashback") ? "Cashback" : "Amount"; ?>:</td>
                <td valign="middle"><?php echo (SITE_CURRENCY_FORMAT <= 3) ? SITE_CURRENCY : ""; ?><input type="text" class="form-control" name="amount" value="<?php echo DisplayMoney($row['amount'], 1); ?>" size="6" /> <?php echo (SITE_CURRENCY_FORMAT > 3) ? SITE_CURRENCY : ""; ?></td>
              </tr>
			  <?php if ($row['payment_type'] == "withdrawal" && $row['transaction_commision'] != "0.0000") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Commission:</td>
                <td valign="middle"><?php echo DisplayMoney($row['transaction_commision']); ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Amount to pay:</td>
                <td valign="middle"><span class="amount" style="background: #A1DB36"><?php echo DisplayMoney($row['amount']-$row['transaction_commision']); ?></span></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="middle"><?php echo $row['payment_date']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Comment:</td>
                <td valign="middle"><textarea cols="55" rows="4" name="reason" class="form-control"><?php echo strip_tags($row['reason']); ?></textarea></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Status:</td>
                <td valign="middle">
					<select name="status" id="status" class="selectpicker">
						<option value="confirmed" <?php if ($row['status'] == "confirmed") echo "selected"; ?>>confirmed</option>
						<option value="pending" <?php if ($row['status'] == "pending") echo "selected"; ?>>pending</option>
						<option value="declined" <?php if ($row['status'] == "declined") echo "selected"; ?>>declined</option>
					</select>
				</td>
              </tr>
			<tr>
				<td align="left" valign="middle" class="tb1">&nbsp;</td>
				<td align="left" valign="middle">
					<div class="checkbox">
					<label><input type="checkbox" class="checkbox" name="notification" value="1" <?php if (@$notification == 1) echo "checked=\"checked\""; ?> /> send email notification to member</label>
					</div>
				</td>
			</tr>
            <tr>
              <td>&nbsp;</td>
			  <td align="left" valign="bottom">
				<input type="hidden" name="tid" id="tid" value="<?php echo (int)$row['transaction_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit_payment">
				<input type="submit" class="btn btn-success" name="process" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" />
			  </td>
            </tr>
          </table>
		  </form>
      
	  <?php }else{ ?>
			<div class="alert alert-info">Sorry, no payment found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>