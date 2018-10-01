<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/auth_operator.inc.php");
	require_once("../inc/config.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	if (isset($_GET['type']) && $_GET['type'] == "cbb") $cpage = 7; else $cpage = 9;

	CheckAdminPermissions($cpage);
	
	$statuses_arr = array("pending", "confirmed", "declined", "cancelled", "timeout", "paid"); //dev
	
	
	if (isset($_POST['action']) && $_POST['action'] == "change_status")
	{
		unset($errors);
		$errors = array();

		$exchange_id		= (int)getPostParameter('payment_id');
		$send_notification 	= (int)getPostParameter('send_notification');
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));
		
		if (in_array($status, $statuses_arr))
		{
			$tsql = "SELECT * FROM exchangerix_exchanges WHERE exchange_id='$exchange_id' LIMIT 1";
			$tresult = smart_mysql_query($tsql);
			$ttotal = mysqli_num_rows($tresult);

			if ($ttotal > 0)
			{
				$trow = mysqli_fetch_array($tresult);
			}		
			
			if ($status == "confirmed")
			{
				//smart_mysql_query("UPDATE exchangerix_currencies SET total_exchanges=total_exchanges+1 WHERE currency_id='".(int)$trow['from_currency_id']."' LIMIT 1");
				smart_mysql_query("UPDATE exchangerix_exdirections SET today_exchanges=today_exchanges+1, total_exchanges=total_exchanges+1, last_exchange_date=NOW() WHERE exdirection_id='".(int)$trow['exchange_id']."' LIMIT 1");	
				//smart_mysql_query("UPDATE exchangerix_settings SET setting_value='' WHERE setting_key='total_exchanges_usd' LIMIT 1");
				//dev
			}elseif ($status == "declined" || $status == "cancelled")
			{
				// update reserve
				smart_mysql_query("UPDATE exchangerix_currencies SET reserve+='".floatval($trow['receive_amount'])."', status='pending' WHERE currency_id='".(int)$trow['to_currency']."' LIMIT 1");
			}
			
			smart_mysql_query("UPDATE exchangerix_exchanges SET status='$status', updated=NOW(), process_date=NOW() WHERE exchange_id='$exchange_id' LIMIT 1");
		}
		
		if ($send_notification == 1)
		{
			////////////////////////////////  Send notification  ////////////////////////
				if ($status == "paid")
				{
					$etemplate = GetEmailTemplate('payment_success');
				}
				elseif ($status == "declined")
				{
					$etemplate = GetEmailTemplate('payment_declined');
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
		
		header("Location: exchanges.php?msg=updated");
		exit();
	}
	

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS payment_date, DATE_FORMAT(updated, '".DATE_FORMAT." %h:%i %p') AS updated_date, DATE_FORMAT(process_date, '".DATE_FORMAT." %h:%i %p') AS processed_date FROM exchangerix_exchanges WHERE exchange_id='$id' LIMIT 1";
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
		
		<h2><i class="fa fa-money" aria-hidden="true"></i> Exchange Details &nbsp; #<?php echo $row['reference_id']; ?></h2>
		
		<!-- proof //zip //dev -->
		
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
			
			<div class="row" style="background:#F9F9F9">
			<div class="col-md-4" style="border-right: 2px solid #FFF;">
				
			<table width="100%" style="padding: 10px 0;" cellpadding="3" cellspacing="5" border="0" align="center">
			  <tr>
                <td width="44%" valign="middle" align="left" class="tb1">ID:</td>
                <td valign="middle"><?php echo $row['exchange_id']; ?></td>
              </tr>
			  <tr>
                <td valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="middle"><?php echo $row['reference_id']; ?></td>
              </tr>	
              <?php if ($row['user_id'] > 0) { ?>
              <!--
	          <tr>
                <td valign="middle" align="left" class="tb1">Username:</td>
                <td valign="middle"><?php echo $row['username']; ?></td>
              </tr>
              -->
              <tr>
                <td valign="middle" align="left" class="tb1">Member:</td>
                <td valign="middle"><i class="fa fa-user-circle" aria-hidden="true"></i>  <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id']); ?></a></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-exchange"></i> Exchanges:</td>
                <td valign="middle"><a href="exchanges.php?filter=<?php echo $row['user_id']; ?>&search_type=member&action=filter"><span class="badge" style="background: #89b601"><?php echo GetUserExchangesTotal($row['user_id']); ?></span></a></td>
              </tr>              
              <?php }else{ ?>
              <tr>
                <td valign="middle" align="left" class="tb1">User:</td>
                <td valign="middle"><i class="fa fa-user-o" aria-hidden="true"></i> Visitor</td>
              </tr>              
              <?php } ?>              		  
              <!--
              <?php if ($row['client_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">User's Details:</td>
                <td valign="middle"><?php echo $row['client_details']; ?></td>
              </tr> 
              <?php } ?>             
               <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-envelope-o"></i> User's Email:</td>
                <td valign="middle"><a href="mailto:<?php echo $row['client_email']; ?>"><?php echo $row['client_email']; ?></a></td>
              </tr>
              -->
              <?php if ($row['country_code'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Country:</td>
                <td valign="middle"><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" /> <?php echo $row['country_code']; ?></td>
              </tr>
              <?php } ?>
              <!--
              <tr>
                <td valign="middle" align="left" class="tb1">Email:</td>
                <td valign="middle"><a href="email2users.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['email']; ?></a></td>
              </tr>
              -->
			  <?php if ($row['ref_id'] > 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Referral ID:</td>
                <td valign="middle"><a href="user_details.php?id=<?php echo $row['ref_id']; ?>"><i class="fa fa-user-circle-o" aria-hidden="true" style="color: #4793c3"></i> <?php echo GetUsername($row['ref_id']); ?></a></td>
              </tr>
              <?php } ?>
			  <?php if ($row['reason'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Comment:</td>
                <td style="color: #777;" valign="middle"><?php echo $row['reason']; ?></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-clock-o"></i> Created:</td>
                <td valign="middle"><?php echo $row['payment_date']; ?></td>
              </tr>
			  <?php if ($row['updated'] != "0000-00-00 00:00:00" && ($row['created'] != $row['updated'])) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1"><i class="fa fa-clock-o"></i> Updated:</td>
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
							case "confirmed": echo "<span class='label label-success'><i class='fa fa-check'></i> confirmed</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting confirmation</span>"; break;
							case "waiting": echo "<span class='label label-default'>waiting for payment</span>"; break;
							case "declined": echo "<span class='label label-danger'><i class='fa fa-times'></i> declined</span>"; break;
							case "failed": echo "<span class='label label-danger'>failed</span>"; break;
							case "cancelled": echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
							case "timeout": echo "<span class='label label-danger'><i class='fa fa-times'></i> timeout</span>"; break;
							case "request": echo "<span class='label label-warning'>awaiting approval</span>"; break;
							case "paid": echo "<span class='label label-success'>paid</span>"; break;
							default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
						}
					?>					
				</td>
              </tr>
			</table>
			<br>
			
			<?php if ($row['status'] != "confirmed") { ?>
			<form action="" method="post">
			<div style="background:#f2f7f9; padding: 10px 0; border: 1px solid #e6f2f7; border-radius: 7px;">
			<table width="95%" cellpadding="3" cellspacing="5" border="0" align="center">
              <tr>
                <td valign="top" align="center">
	                
	                <h3 style="color: #359bc7"><i class="fa fa-cog" aria-hidden="true"></i> Change Status</h3>
                
					<select name="status" id="status" class="form-control" required>
						<option value="">--- select status ---</option>
						<?php if ($row['status'] != "pending") { ?><option value="pending">Pending</option><?php } ?>
						<?php if ($row['status'] != "confirmed") { ?><option value="confirmed">Confirmed</option><?php } ?>
						<?php if ($row['status'] != "declined") { ?><option value="declined">Declined</option><?php } ?>
					</select>
					<p><div class="checkbox"><label><input type="checkbox" class="checkbox" name="send_notification" value="1" <?php if (!$_POST['action'] || getPostParameter('send_notification') == 1) echo "checked=\"checked\""; ?> /> send email notification to client</label></div></p>            
					<input type="hidden" name="payment_id" value="<?php echo (int)$row['exchange_id']; ?>" />
					<input type="hidden" name="action" value="change_status" />
					<button type="submit" class="btn btn-info" name="proceed"><i class="fa fa-refresh"></i> Proceed Exchange</button>
	                <br><br>
	                
				</td>
              </tr>              		  
			</table>
			</div>
			</form>
			<br>
			<?php } ?>						
	
						
					</div>
					<div class="col-md-8" style="background:#F9F9F9">
		
						
					<table width="100%" style="background:#F9F9F9" cellpadding="3" cellspacing="5" border="0" align="center">
					<tr>
						<td align="right" valign="top"><h1><img src="images/currencies/<?php echo $send_row['image']; ?>" style="border-radius: 50%" width="40" height="40" /> <?php echo substr($row['from_currency'], 0, -4); ?></h1></td>
						<td align="center" valign="top"><a href="exdirection_details.php?id=<?php echo $row['exdirection_id']; ?>"><h1>&nbsp; <i id="itooltip" title="<?php echo $row['status']; ?> status" class="fa fa-refresh" aria-hidden="true" style="color: #555 <?php //echo $i_color; ?>"></i> &nbsp;</h1></a></td>
						<td align="left" valign="top"><h1><img src="images/currencies/<?php echo $receive_row['image']; ?>" style="border-radius: 50%" width="40" height="40" /> <?php echo substr($row['to_currency'], 0, -4); ?></h1></td>
					</tr>
					<tr>
						<td align="right"><h3><?php echo floatval($row['exchange_amount']); ?> <sup><?php echo substr($row['from_currency'], -4); ?></sup></h3></td>
						<td align="center"><h3><i class="fa fa-long-arrow-right" aria-hidden="true"></i></h3></td>
						<td align="left"><h3><?php echo floatval($row['receive_amount']); ?> <sup><?php echo substr($row['to_currency'], -4); ?></sup></h3></td>
					</tr>
					<tr>
						<td colspan="3" align="center">
							<b class="badge" style="font-weight: normal; background: #c7c7c7">Exchange Rate: <?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?></b>			
							<?php if ($row['exchange_fee'] != "" && $row['exchange_fee'] != "0.0000") { ?>		
								<b class="badge" style="font-weight: normal; background: #f9fcee">(Exchange Fee: <?php echo floatval($row['exchange_fee']); ?> <?php echo $row['from_currency']; ?>)</b>
							<?php } ?>
							<br><br>					
						</td>
					</tr>					
					</table>					
						
						
					<div class="row" style="border-top: 1px solid #EEE;" align="center">
						<div class="col-md-6 text-center" style="background: #fcfffc; border-right: 1px solid #FFF">
							<h3 class="text-center"><i class="fa fa-file-text-o"></i> Payment Details</h3>
							<br>
							<i class="fa fa-user-circle"></i> <?php echo $row['client_details']; ?><br>
							<?php echo $row['email']; ?><br>
							<b><?php echo $row['from_account']; ?>&nbsp;</b>
							<?php if ($row['proof'] != "") { ?><hr> <h4><i class="fa fa-paperclip fa-lg"></i> Payment Proof Image</h4> <a href="<?php echo SITE_URL; ?>uploads/<?php echo $row['proof']; ?>" data-lightbox="image-1" data-title="Payment Proof"><img src="<?php echo SITE_URL; ?>uploads/<?php echo $row['proof']; ?>" width="300" height="100" style="opacity: 0.4"></a><?php } ?>
							<br>
						</div>
						<div class="col-md-6 text-center" style="background: #f1f6ee">
							<h3 class="text-center">Receive to Account <i class="fa fa-arrow-down" aria-hidden="true" style="color: #5cb85c"></i></h3>
							<br>
							<b><?php echo $row['to_account']; ?>&nbsp;</b>
							<br><br>
						</div>				
					</div>
					<br><br>
					
					 <?php if ($row['payment_details'] != "") { ?>
					 <div class="row" style="border-top: 1px solid #EEE;" align="center">
						 <div class="col-md-12 text-center" 
			              	<h3>Payment Details</h3>
						  	<?php echo $row['payment_details']; ?>
					 	</div>
					 </div>
					 <?php } ?>						

					 <?php if ($row['payment_proof'] != "") { ?>
					 <div class="row" style="border-top: 1px solid #EEE;" align="center">
						 <div class="col-md-12 text-center" 
			              	<h3>Payment Proof</h3>
						  	<i class="fa fa-paperclip"></i> <a href="<?php echo SITE_URL; ?>proof/<?php echo $row['payment_proof']; ?>" target="_blank"><?php echo $row['payment_proof']; ?></a>
					 	</div>
					 </div>
					 <?php } ?>
						
						
					</div>
				</div>
            

                <p class="text-center">
					<?php if ($row['payment_type'] == "withdrawal" && $row['status'] == "request") { ?>
						<a class="btn btn-success" href="exchange_process.php?id=<?php echo $row['exchange_id']; ?>"><i class="fa fa-refresh"></i> Proceed Exchange</a>
					<?php }else{ ?>
						<a class="btn btn-success" href="exchange_edit.php?id=<?php echo $row['exchange_id']; ?>&type=<?php echo $_GET['type']; ?>"><i class="fa fa-pencil-square-o"></i> Edit Exchange</a>
					<?php } ?>
					<a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back <i class="fa fa-angle-right" aria-hidden="true"></i></a>
					<a class="btn btn-danger pull-right" href="#" onclick="if (confirm('Are you sure you really want to delete this exchange?') )location.href='exchanges.php?id=<?php echo $row['exchange_id']; ?>&action=delete';"><i class="fa fa-times" aria-hidden="true"></i> Delete</a>
				</p>

      <?php }else{ ?>
      		<h2>Exchange Details</h2>
			<div class="alert alert-info">Sorry, no exchange found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>