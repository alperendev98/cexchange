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

		$exchange_id	= (int)getPostParameter('tid');
		$order_total	= mysqli_real_escape_string($conn, getPostParameter('order_total'));
		$amount_send		= mysqli_real_escape_string($conn, getPostParameter('amount_send'));
		$amount_receive		= mysqli_real_escape_string($conn, getPostParameter('amount_receive'));
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));
		$reason			= mysqli_real_escape_string($conn, nl2br(getPostParameter('reason')));
		$notification	= (int)getPostParameter('notification');
		
		//client detaisl //dev

		if (!$status)
		{
			$errs[] = "Please select payment status";
		}
		else
		{
			if (!is_numeric($amount_send) || !is_numeric($amount_receive))
			{
				$errs[] = "Please enter correct amount";
				$amount = "";
			}

			/*if ($order_total)
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
			}*/
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE exchangerix_exchanges SET ".$add_sql." exchange_amount='$amount_send', receive_amount='$amount_receive', status='$status', reason='$reason', updated=NOW() WHERE exchange_id='$exchange_id' LIMIT 1";
			$result = smart_mysql_query($sql);

				if ($notification == 1)
				{
					$tsql = "SELECT * FROM exchangerix_exchanges WHERE exchange_id='$exchange_id' LIMIT 1";
					$tresult = smart_mysql_query($tsql);
					$ttotal = mysqli_num_rows($tresult);

					if ($ttotal > 0)
					{
						$trow = mysqli_fetch_array($tresult);
					}

					// send email ///////////////////////////////////////////////////////////////
					/*$etemplate = GetEmailTemplate('manual_credit');
					$esubject = $etemplate['email_subject'];
					$emessage = $etemplate['email_message'];

					$emessage = str_replace("{transaction_id}", $reference_id, $emessage);
					$emessage = str_replace("{first_name}", GetUsername($trow['user_id'], $type = 3), $emessage);
					$emessage = str_replace("{payment_type}", $trow['payment_type'], $emessage);
					$emessage = str_replace("{amount}", DisplayMoney($amount), $emessage);
					$emessage = str_replace("{status}", $status, $emessage);
					$emessage = str_replace("{reason}", $reason, $emessage);
					$to_email = $urow['fname'].' '.$urow['lname'].' <'.$urow['email'].'>';

					SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);*/
					//////////////////////////////////////////////////////////////////////////////
				}
			
			header("Location: exchanges.php?msg=updated");
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
		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS payment_date FROM exchangerix_exchanges WHERE exchange_id='$id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Edit Exchange";
	require_once ("inc/header.inc.php");

?>
    
		<h2><i class="fa fa-refresh"></i> Edit Exchange</h2>

		<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<?php if (isset($errormsg)) { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>

			<form action="" method="post" name="form1">
            <table width="100%" style="background:#F9F9F9; border-radius: 7px;" cellpadding="3" cellspacing="3" border="0" align="center">
              <!--
			  <tr>
                <td width="17%" valign="middle" align="left" class="tb1">Exchange ID:</td>
                <td valign="middle"><?php echo $row['exchange_id']; ?></td>
              </tr>
			  -->
              <tr>
                <td width="17%"  valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="middle"><b><?php echo $row['reference_id']; ?></b></td>
              </tr>
              <?php if ($row['user_id'] > 0) { ?>
              <tr>
                <td  valign="middle" align="left" class="tb1"><i class="fa fa-user-o" aria-hidden="true"></i> Member:</td>
                <td valign="middle"><a href="user_details.php?id=<?php echo $row['user_id']; ?>" class="user"><?php echo GetUsername($row['user_id']); ?></a></td>
              </tr>
              <?php }else{ ?>
              <tr>
                <td  valign="middle" align="left" class="tb1"><i class="fa fa-user-circle"></i> Member:</td>
                <td valign="middle">Visitor</td>
              </tr>              
              <?php } ?>
			  <?php if ($row['payment_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Details:</td>
                <td valign="middle"><?php echo $row['payment_details']; ?></td>
              </tr>
			  <?php } ?>
			  <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-arrow-up" aria-hidden="true" style="color: #8dc6fb"></i> Amount Send:</td>
                <td valign="middle"><input type="text" class="form-control" name="amount_send" value="<?php echo $row['exchange_amount']; ?>" size="15" /> <?php echo $row['from_currency']; ?></td>
              </tr>
			  <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-arrow-down" aria-hidden="true" style="color: #5cb85c"></i> Amount Receive:</td>
                <td valign="middle"><input type="text" class="form-control" name="amount_receive" value="<?php echo $row['receive_amount']; ?>" size="15" /> <?php echo $row['to_currency']; ?></td>
              </tr>	              			  
              <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-clock-o"></i> Created:</td>
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
						<option value="waiting" <?php if ($row['status'] == "waiting") echo "selected"; ?>>waiting for payment</option>
						<option value="timeout" <?php if ($row['status'] == "timeout") echo "selected"; ?>>timeout</option>
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
				<input type="hidden" name="tid" id="tid" value="<?php echo (int)$row['exchange_id']; ?>" />
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