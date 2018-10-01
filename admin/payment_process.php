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

	$cpage = 10;

	CheckAdminPermissions($cpage);

	if (isset($_POST["action"]) && $_POST["action"] == "process_payment")
	{
		unset($errors);
		$errors = array();

		$transaction_id		= (int)getPostParameter('tid');
		$user_id			= (int)getPostParameter('uid');
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));
		$reason				= mysqli_real_escape_string($conn, nl2br(getPostParameter('reason')));
		$notification		= (int)getPostParameter('notification');

		if (!($status))
		{
			$errs[] = "Please select payment status";
		}

		if (count($errs) == 0)
		{
			$tsql = "SELECT * FROM exchangerix_transactions WHERE transaction_id='$transaction_id' LIMIT 1";
			$tresult = smart_mysql_query($tsql);
			$ttotal = mysqli_num_rows($tresult);

			if ($ttotal > 0)
			{
				$trow = mysqli_fetch_array($tresult);
			}
			
			if ($status == "paid" && $ttotal > 0)
			{
				$uresult = smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_id='$user_id' LIMIT 1");
				if (mysqli_num_rows($uresult) > 0)
				{
					$urow = mysqli_fetch_array($uresult);

					// Confirm Refer a Friend Bonus //
					if ($urow['ref_id'] > 0)
					{
						smart_mysql_query("UPDATE exchangerix_transactions SET status='confirmed', process_date=NOW() WHERE user_id='".(int)$urow['ref_id']."' AND ref_id='".(int)$urow['user_id']."' AND payment_type='friend_bonus' AND status='pending' LIMIT 1");

						// Confirm referral commission
						if (REFERRAL_COMMISSION > 0 && $trow['payment_type'] == "withdrawal")
						{
							$reference_id = GenerateReferenceID();
							$commission_amount = CalculatePercentage($trow['amount'], REFERRAL_COMMISSION);
							smart_mysql_query("INSERT INTO exchangerix_transactions SET reference_id='$reference_id', user_id='".(int)$urow['ref_id']."', ref_id='".(int)$urow['user_id']."', amount='$commission_amount', payment_type='referral_commission', status='confirmed', created=NOW(), process_date=NOW()");
						}
					}
				}
			}

			$sql = "UPDATE exchangerix_transactions SET status='$status', reason='$reason', process_date=NOW() WHERE transaction_id='$transaction_id' LIMIT 1";
			$result = smart_mysql_query($sql);

			////////////////////////////////  Send notification  ////////////////////////
			if ($notification == 1)
			{
				if ($status == "paid")
				{
					$etemplate = GetEmailTemplate('cashout_paid');
				}
				elseif ($status == "declined")
				{
					$etemplate = GetEmailTemplate('cashout_declined');
				}

				$esubject = $etemplate['email_subject'];
				$emessage = $etemplate['email_message'];

				$emessage = str_replace("{transaction_id}", $trow['reference_id'], $emessage);
				$emessage = str_replace("{first_name}", $urow['fname'], $emessage);
				$emessage = str_replace("{commission}", DisplayMoney($trow['transaction_commision']), $emessage);
				if ($trow['transaction_commision'] != "0.0000")
					$amount = $trow['amount']-$trow['transaction_commision'];
				else
					$amount = $trow['amount'];
				$emessage = str_replace("{amount}", DisplayMoney($amount), $emessage);
				$emessage = str_replace("{reason}", $reason, $emessage);
				$to_email = $urow['fname'].' '.$urow['lname'].' <'.$urow['email'].'>';

				SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
			}

			header("Location: cashout_requests.php?msg=processed");
			exit();
		}
		else
		{
			$errormsg = "";
			foreach ($errs as $errorname)
				$errormsg .= $errorname."<br/>";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT t.*, DATE_FORMAT(t.created, '".DATE_FORMAT." %h:%i %p') AS payment_date, u.username, u.fname, u.lname FROM exchangerix_transactions t, exchangerix_users u WHERE t.user_id=u.user_id AND t.transaction_id='$id' AND t.status<>'confirmed' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Process Payment";
	require_once ("inc/header.inc.php");

?>

		<h2>Process Payment</h2>

		<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<?php if (isset($errormsg)) { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>

			<form action="" method="post" name="form1">
            <table width="100%" style="background:#F9F9F9" style="border-radius: 7px;" cellpadding="3" cellspacing="3" border="0" align="center">
              <!--
			  <tr>
                <td width="14%" valign="middle" align="left" class="tb1">Payment ID:</td>
                <td valign="top"><?php echo $row['transaction_id']; ?></td>
              </tr>
			  -->
              <tr>
                <td width="14%"  valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="top"><?php echo $row['reference_id']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Username:</td>
                <td valign="top"><?php echo $row['username']; ?></td>
              </tr>
              <tr>
                <td  valign="middle" align="left" class="tb1">Member:</td>
                <td valign="top"><a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Type:</td>
                <td valign="top">
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
						?>
						<?php if ($row['ref_id'] > 0) { ?> | <a href="user_details.php?id=<?php echo $row['ref_id']; ?>" class="user"><?php echo GetUsername($row['ref_id']); ?></a><?php } ?>
				</td>
              </tr>
			  <?php if ($row['payment_type'] == "withdrawal" && $row['payment_method'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Method:</td>
                <td valign="top">
					<?php if ($row['payment_method'] == 1) { ?><img src="images/paypal.png" align="absmiddle" />&nbsp;<?php }else{ ?>
						<?php echo GetPaymentMethodByID($row['payment_method']); ?>
					<?php } ?>
				</td>
              </tr>
			  <?php } ?>
			  <?php if ($row['payment_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Details:</td>
                <td valign="top"><?php echo $row['payment_details']; ?></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Amount:</td>
                <td valign="top"><b><?php echo DisplayMoney($row['amount']); ?></b></td>
              </tr>
			  <?php if ($row['payment_type'] == "withdrawal" && $row['transaction_commision'] != "0.0000") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Commission:</td>
                <td valign="top"><?php echo DisplayMoney($row['transaction_commision']); ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1"><b>Amount to pay</b>:</td>
                <td valign="top"><span class="amount" style="background: #A1DB36"><?php echo DisplayMoney($row['amount']-$row['transaction_commision']); ?></span></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Status:</td>
                <td valign="top">
					<?php
						switch ($row['status'])
						{
							case "confirmed": echo "<span class='confirmed_status'>confirmed</span>"; break;
							case "pending": echo "<span class='pending_status'>pending</span>"; break;
							case "declined": echo "<span class='declined_status'>declined</span>"; break;
							case "failed": echo "<span class='failed_status'>failed</span>"; break;
							case "request": echo "<span class='request_status'>awaiting approval</span>"; break;
							default: echo "<span class='payment_status'>".$row['status']."</span>"; break;
						}
					?>
				</td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="top"><?php echo $row['payment_date']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Mark as:</td>
                <td valign="top">
					<select name="status" id="status" onchange="javascript:hiddenDiv('status','reason')" class="form-control">
						<option value="paid">paid</option>
						<option value="declined">declined</option>
					</select>
				</td>
              </tr>
              <tr id="reason" style="display: none;">
                <td valign="middle" align="left" class="tb1">Reason:</td>
                <td valign="top"><textarea cols="55" rows="5" name="reason" class="form-control"><?php echo getPostParameter('reason'); ?></textarea></td>
            </tr>
            <tr>
                <td valign="middle" align="left" class="tb1">&nbsp;</td>
                <td valign="top"><input type="checkbox" class="checkbox" name="notification" value="1" <?php if (getPostParameter('notification') == 1 || !$notification) echo "checked=\"checked\""; ?> /> send email notification to member</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td align="left" valign="bottom">
				<input type="hidden" name="tid" id="tid" value="<?php echo (int)$row['transaction_id']; ?>" />
				<input type="hidden" name="uid" id="uid" value="<?php echo (int)$row['user_id']; ?>" />
				<input type="hidden" name="action" id="action" value="process_payment">
				<input type="submit" class="btn btn-success" name="process" value="Process Payment" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" />
			  </td>
            </tr>
          </table>
		  </form>
      
	  	<script type="text/javascript">
		<!--
			function hiddenDiv(id,showid){
				if(document.getElementById(id).value == "declined"){
					document.getElementById(showid).style.display = ""
				}else{
					document.getElementById(showid).style.display = "none"
				}
			}
		-->
		</script>

	  <?php }else{ ?>
			<div class="alert alert-info">Sorry, no payment found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>