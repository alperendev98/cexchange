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

	if (isset($_GET['type']) && $_GET['type'] == "cbb") $cpage = 7; else $cpage = 9;

	CheckAdminPermissions($cpage);
	
	$statuses_arr = array("pending", "confirmed", "declined", "cancelled", "timeout", "paid"); //dev
	
	
	if (isset($_POST['action']) && $_POST['action'] == "change_status")
	{
		unset($errors);
		$errors = array();

		$transaction_id		= (int)getPostParameter('payment_id');
		$send_notification 	= (int)getPostParameter('send_notification');
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));
		
		if (in_array($status, $statuses_arr))
		{
			smart_mysql_query("UPDATE exchangerix_transactions SET status='$status' WHERE transaction_id='$transaction_id' LIMIT 1");
		}
		
		if ($send_notification == 1)
		{
			// SEND EMAIL NOTIFICATION
			//dev
		}
		
		header("Location: payments.php?msg=updated");
		exit();
	}
	

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS payment_date, DATE_FORMAT(updated, '".DATE_FORMAT." %h:%i %p') AS updated_date, DATE_FORMAT(process_date, '".DATE_FORMAT." %h:%i %p') AS processed_date FROM exchangerix_transactions WHERE transaction_id='$id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Exchange Details";
	require_once ("inc/header.inc.php");

?>
    
		<?php
			
			if ($total > 0) { 
			
			$row = mysqli_fetch_array($result);
			
			$send_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='".(int)$row['from_currency_id']."' LIMIT 1"));
			$receive_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='".(int)$row['to_currency_id']."' LIMIT 1"));
			 
		?>
		
		<h2>Exchange Details &nbsp; #<?php echo $row['reference_id']; ?></h2>
		
			<?php
				
				switch ($row['status'])
				{
					case "pending": $i_color = "#f7b400"; break;
					case "confirmed": $i_color = "green"; break;
					case "declined": $i_color = "red"; break;
					case "cancelled": $i_color = "red"; break;
					case "timeout": $i_color = "red"; break;
				}
				
			?>
			
			<table width="100%" cellpadding="3" cellspacing="5" border="0" align="center">
			<tr>
			<td width="30%" align="left" valign="top" style="background:#F9F9F9; border-right: 2px solid #FFF;">
			<br>
			<table width="100%" style="background:#F9F9F9; padding: 10px 0;" cellpadding="3" cellspacing="5" border="0" align="center">
			  <tr>
                <td width="36%" valign="middle" align="left" class="tb1">ID:</td>
                <td valign="middle"><?php echo $row['transaction_id']; ?></td>
              </tr>
			  <tr>
                <td width="36%" valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="middle"><?php echo $row['reference_id']; ?></td>
              </tr>			  
              <?php if ($row['client_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">User's Details:</td>
                <td valign="middle"><?php echo $row['client_details']; ?></td>
              </tr> 
              <?php } ?>             
               <tr>
                <td valign="middle" align="left" class="tb1">User's Email:</td>
                <td valign="middle"><a href="mailto:<?php echo $row['client_email']; ?>"><?php echo $row['client_email']; ?></a></td>
              </tr>                           			 
              <tr>
                <td valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="middle"><?php echo $row['reference_id']; ?></td>
              </tr>
              <?php if ($row['country_code'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Country:</td>
                <td valign="middle"><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" /> <?php echo $row['country_code']; ?></td>
              </tr>
              <?php } ?>              
              <?php if ($row['user_id'] > 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Username:</td>
                <td valign="middle"><?php echo $row['username']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Member:</td>
                <td valign="middle"><i class="fa fa-user-cirle" aria-hidden="true"></i>  <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
              </tr>
              <!--
              <tr>
                <td valign="middle" align="left" class="tb1">Exchanges:</td>
                <td valign="middle"><a href="user_payments.php?id=<?php echo $row['user_id']; ?>"><?php //echo GetUserClicksTotal($row['user_id']); //dev ?></a></td>
              </tr>
              -->             
              <?php }else{ ?>
              <tr>
                <td valign="middle" align="left" class="tb1">User:</td>
                <td valign="middle"><i class="fa fa-user-o" aria-hidden="true"></i> Visitor</td>
              </tr>              
              <?php } ?>
              <!--
              <tr>
                <td valign="middle" align="left" class="tb1">Email:</td>
                <td valign="middle"><a href="email2users.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['email']; ?></a></td>
              </tr>
              -->
              <!--
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Type:</td>
                <td valign="middle">
						<?php
								switch ($row['payment_type'])
								{
									case "withdrawal":			echo PAYMENT_TYPE_WITHDRAWAL; break;
									case "referral_commission": echo PAYMENT_TYPE_RCOMMISSION; break;
									case "friend_bonus":		echo PAYMENT_TYPE_FBONUS; break;
									case "signup_bonus":		echo PAYMENT_TYPE_SBONUS; break;
									default:					echo $row['payment_type']; break;
								}
						?>
				</td>
              </tr>
              -->
			  <?php if ($row['ref_id'] > 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Referral ID:</td>
                <td valign="middle"><a href="user_details.php?id=<?php echo $row['ref_id']; ?>" class="user"><?php echo GetUsername($row['ref_id']); ?></a></td>
              </tr>
              <?php } ?>
			  <?php if ($row['payment_type'] == "withdrawal" && $row['payment_method'] > 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Method:</td>
                <td valign="middle">
					<?php if ($row['payment_method'] == 1) { ?><img src="images/paypal.png" align="absmiddle" />&nbsp;<?php }else{ ?>
						<?php echo GetPaymentMethodByID($row['payment_method']); ?>
					<?php } ?>
                </td>
              </tr>
			  <?php } ?>
			  <?php if ($row['payment_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Details:</td>
                <td valign="middle"><?php echo $row['payment_details']; ?></td>
              </tr>
			  <?php } ?>
			  <?php /*if ($row['transaction_amount'] != "0.0000") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Order Total:</td>
                <td valign="middle"><?php echo DisplayMoney($row['transaction_amount']); ?></td>
              </tr>
			  <?php }*/ ?>
			  <!--
              <tr>
                <td valign="middle" align="left" class="tb1"><?php echo ($row['payment_type'] == "cashback") ? "Cashback" : "Amount"; ?>:</td>
                <td valign="middle"><b><?php echo DisplayMoney($row['amount']); ?></b></td>
              </tr>
              -->
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
			  <?php if ($row['reason'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Comment:</td>
                <td style="color: #777;" valign="middle"><?php echo $row['reason']; ?></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="middle"><?php echo $row['payment_date']; ?></td> <!-- //dev 10 minutes ago -->
              </tr>
			  <?php if ($row['updated'] != "0000-00-00 00:00:00" && ($row['created'] != $row['updated'])) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Date Updated:</td>
                <td valign="middle"><?php echo $row['updated_date']; ?></td>
              </tr>
			  <?php } ?>
			  <?php if ($row['payment_type'] == "withdrawal" && ($row['status'] == "declined" || $row['status'] == "paid") && $row['process_date'] != "0000-00-00 00:00:00") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Process Date:</td>
                <td valign="middle"><?php echo $row['processed_date']; ?></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Status:</td>
                <td valign="middle">
					<?php
						switch ($row['status'])
						{
							case "confirmed": echo "<span class='confirmed_status'>confirmed</span>"; break;
							case "pending": echo "<span class='pending_status'>pending</span>"; break;
							case "declined": echo "<span class='declined_status'>declined</span>"; break;
							case "failed": echo "<span class='failed_status'>failed</span>"; break;
							case "request": echo "<span class='request_status'>awaiting approval</span>"; break;
							case "paid": echo "<span class='paid_status'>paid</span>"; break;
							default: echo "<span class='payment_status'>".$row['status']."</span>"; break;
						}
					?>
					<?php if ($row['status'] == "unknown") { ?><span class="note" title="please check settings"></span><?php } ?>
				</td>
              </tr>
			</table>
			<br>
			
			<?php if ($row['status'] != "confirmed") { ?>
			<form action="" method="post">
			<table width="95%" style="background:#EEE; padding: 10px 0; border-radius: 7px;" cellpadding="3" cellspacing="5" border="0" align="center">
              <tr>
                <td valign="top" align="center">
	                
	                <h3><i class="fa fa-cog" aria-hidden="true"></i> Change Status</h3>
                
					<select name="status" id="status" class="form-control">
						<option value="">--- select status ---</option>
						<?php if ($row['status'] != "pending") { ?><option value="pending">Pending</option><?php } ?>
						<?php if ($row['status'] != "confirmed") { ?><option value="confirmed">Confirmed</option><?php } ?>
						<?php if ($row['status'] != "declined") { ?><option value="declined">Declined</option><?php } ?>
					</select>
					<p><div class="checkbox"><label><input type="checkbox" class="checkbox" name="send_notification" value="1" <?php if (!$_POST['action'] || getPostParameter('send_notification') == 1) echo "checked=\"checked\""; ?> /> send notification to client</label></div></p>            
					<input type="hidden" name="payment_id" value="<?php echo (int)$row['transaction_id']; ?>" />
					<input type="hidden" name="action" value="change_status" />
					<input type="submit" class="btn btn-success" name="proceed" value="Proceed Payment"> 
	                <br><br>
	                
				</td>
              </tr>              		  
			</table>
			</form>
			<br>
			<?php } ?>						
	
						
					</td>
					<td align="left" width="70%" valign="top" style="background:#F9F9F9;">
		
						
					<table width="100%" style="background:#F9F9F9;" cellpadding="3" cellspacing="5" border="0" align="center">
					<tr>
						<td align="right" valign="top"><h1><img src="images/currencies/<?php echo $send_row['image']; ?>" width="40" height="40" /> <?php echo $row['from_currency']; ?></h1></td>
						<td align="center" valign="top"><a href="exdirection_details.php?id=<?php echo $row['exdirection_id']; ?>"><h1>&nbsp; <i id="itooltip" title="<?php echo $row['status']; ?>" class="fa fa-refresh" aria-hidden="true" style="color: #000 <?php //echo $i_color; ?>"></i> &nbsp;</h1></a></td>
						<td align="left" valign="top"><h1><img src="images/currencies/<?php echo $receive_row['image']; ?>" width="40" height="40" /> <?php echo $row['to_currency']; ?></h1></td>
					</tr>
					<tr>
						<td align="right"><h3><?php echo $row['exchange_amount']; ?> <sup><?php echo substr($row['from_currency'], -4); ?></sup></h3></td>
						<td align="center"><h3><i class="fa fa-long-arrow-right" aria-hidden="true"></i></h3></td>
						<td align="left"><h3><?php echo $row['receive_amount']; ?> <sup><?php echo substr($row['to_currency'], -4); ?></sup></h3></td>
					</tr>
					<tr>
						<td colspan="3" align="center">
							<h5>(Exchange Rate: <?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?>)</h5>
							<br>
						</td>
					</tr>					
					</table>					
						
						
					<table width="95%" style="border-top: 1px solid #EEE; border-bottom: 1px solid #EEE" cellpadding="3" cellspacing="5" border="0" align="center">
					<tr>
						<td width="50%" bgcolor="#EEE" style="border-right: 1px solid #DDD" align="center" valign="top">
							<h3 class="text-center"><i class="fa fa-arrow-up" aria-hidden="true"></i> Send from Account</h3>
							<br>
							<h4><?php echo $row['from_account']; ?></h4>
							<br>
						</td>
						<td width="50%" bgcolor="#e1f2e5" align="center" valign="top">
							<h3 class="text-center">Receive to Account <i class="fa fa-arrow-down" aria-hidden="true"></i></h3>
							<br>
							<h4><?php echo $row['to_account']; ?></h4>
							<br>
						</td>				
					</tr>					
					</table>
					<br><br>						
						
						
					</td>
				</tr>
			</table>
			

            

                <p class="text-center">
					<?php if ($row['payment_type'] == "withdrawal" && $row['status'] == "request") { ?>
						<input type="button" class="btn btn-success" name="proceed" value="Proceed Payment" onClick="javascript:document.location.href='payment_process.php?id=<?php echo $row['transaction_id']; ?>'" />
					<?php }else{ ?>
						<input type="button" class="btn btn-success" name="edit" value="Edit Payment" onClick="javascript:document.location.href='payment_edit.php?id=<?php echo $row['transaction_id']; ?>&type=<?php echo $_GET['type']; ?>'" />
					<?php } ?>
					<input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" />
					<input type="button" class="btn btn-danger pull-right" name="delete" value="Delete Payment" onclick="if (confirm('Are you sure you really want to delete this payment?') )location.href='payments.php?id=<?php echo $row['transaction_id']; ?>&action=delete';" title="Delete" />
				</p>

      <?php }else{ ?>
      		<h2>Exchange Details</h2>
			<div class="alert alert-info">Sorry, no payment found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>