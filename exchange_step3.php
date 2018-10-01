<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");
	require_once("inc/pagination.inc.php");
	
	if (REQUIRE_LOGIN == 1 && !isLoggedIn())
	{
		header ("Location: login.php?login");
		exit();
	}

	if (!$_SESSION['rid'])
	{
		header ("Location: index.php");
		exit();		
	}

	if (isset($_SESSION['transaction_id']) && is_numeric($_SESSION['transaction_id']) && $_SESSION['transaction_id'] > 0)
	{
		$exchange_id = (int)$_SESSION['transaction_id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}
	
	if (RESERVE_MINUTES > 0) $a_sql = " DATE_ADD(created, INTERVAL ".(int)RESERVE_MINUTES." MINUTE) AS countdate, "; else $a_sql = "";

	$query = "SELECT *, TIMESTAMPDIFF(MINUTE, created, now()) as time_ago, $a_sql DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS payment_date FROM exchangerix_exchanges WHERE exchange_id='$exchange_id' AND (status='waiting' OR status='pending') LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
		
		if (RESERVE_MINUTES > 0 && $row['time_ago'] >= RESERVE_MINUTES)
		{
			smart_mysql_query("UPDATE exchangerix_exchanges SET status='timeout', updated=NOW() WHERE exchange_id='$exchange_id' LIMIT 1");
			header("Location: payment_declined.php?reason=timeout");
			exit();
		}	
		
		$send_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='".(int)$row['from_currency_id']."' LIMIT 1"));
		$receive_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='".(int)$row['to_currency_id']."' LIMIT 1"));
		$direction_row = mysqli_fetch_array(smart_mysql_query("SELECT *, date(last_exchange_date) AS last_update FROM exchangerix_exdirections WHERE from_currency='".(int)$row['from_currency_id']."' AND to_currency='".(int)$row['to_currency_id']."' AND from_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_send='1' AND (reserve>0 || reserve='') AND status='active') AND to_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_receive='1' AND (reserve>0 || reserve='') AND status='active') AND status='active' LIMIT 1"));	
	
		$ip	= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));
		if (filter_var($ip, FILTER_VALIDATE_IP))
			$user_ip = $ip;
		
		
		$ptitle	= "Exchange ".$send_row['currency_name']." ".$send_row['currency_code']." to ".$receive_row['currency_name']." ".$receive_row['currency_code'];
		
		
		// cancel exchange 
		if (isset($_GET['action']) && $_GET['action'] == "cancel")
		{
			smart_mysql_query("UPDATE exchangerix_exchanges SET status='cancelled', updated=NOW() WHERE exchange_id='$exchange_id' LIMIT 1");
			unset($_SESSION['transaction_id']);
			
			header ("Location: index.php");
			exit();
		}

 		// confirm exchange
 		if (isset($_POST['action']) && $_POST['action'] == "confirm")
 		{
			unset($errs);
			$errs = array();
			
			$payment_details = mysqli_real_escape_string($conn, nl2br(getPostParameter('payment_details')));
			
			if (!($payment_details))
				$errs[] = CBE1_SIGNUP_ERR;
			
			if (count($errs) == 0)
			{
				if (NEW_EXCHANGE_ALERT == 1)
				{
					SendEmail(SITE_ALERTS_MAIL, "New Exchange Completed - ".SITE_TITLE, "Hi,<br>New currency exchange completed.");
				}
				
				//update reserve
				smart_mysql_query("UPDATE exchangerix_currencies SET reserve=reserve-'".floatval($row['receive_amount'])."' WHERE currency_id='".(int)$row['to_currency_id']."' LIMIT 1");
				
				if ($direction_row['last_update'] = date("Y-m-d")) $today_ex_sql = "today_exchanges='1', "; else "today_exchanges=today_exchanges+1, ";
				smart_mysql_query("UPDATE exchangerix_exdirections SET ".$today_ex_sql." total_exchanges=total_exchanges+1, last_exchange_date=NOW() WHERE exdirection_id='".(int)$row['exdirection_id']."' LIMIT 1");
								
				//smart_mysql_query("UPDATE exchangerix_currencies SET reserve=reserve+'".floatval($row['exchange_amount'])."' WHERE currency_id='".(int)$row['from_currency_id']."' LIMIT 1");				
				//dev email
				//smart_mysql_query("UPDATE exchangerix_currencies SET total_exchanges=total_exchanges+1 WHERE currency_id='".(int)$row['from_currency']."' LIMIT 1");
				//smart_mysql_query("UPDATE exchangerix_currencies SET total_exchanges=total_exchanges+1 WHERE currency_id='".(int)$row['to_currency']."' LIMIT 1");
				//smart_mysql_query("UPDATE exchangerix_settings SET setting_value=setting_value+$exchange_amount, WHERE setting_key='total_exchanges_usd' LIMIT 1");
				
				smart_mysql_query("UPDATE exchangerix_exchanges SET from_account='$payment_details', status='pending' WHERE exchange_id='$exchange_id' LIMIT 1"); //is_view = 0
				
				// update proof ///////////////////////
				if ($_FILES['upfile']['tmp_name'])
				{
						if (FILES_MAX_SIZE != "" && is_numeric(FILES_MAX_SIZE)) $files_size = FILES_MAX_SIZE; else $files_size = 2097152; // 2MB
						$files_size_kb 		= round($files_size/1024);
					
						if (is_uploaded_file($_FILES['upfile']['tmp_name']))
						{
							list($width, $height, $type) = getimagesize($_FILES['upfile']['tmp_name']);
						
							$check = getimagesize($_FILES["upfile"]["tmp_name"]);
							if ($check === false) $errs[] = "File is not an image";
		
							if ($_FILES['upfile']['size'] > $files_size)
							{
								$errs[] = "The image file size is too big. It exceeds $files_size_kb Kb";
							}
							elseif (preg_match('/\\.(png|jpg|jpeg|gif)$/i', $_FILES['upfile']['name']) != 1)
							{
								$errs[] = "Please upload image file only";
								unlink($_FILES['upfile']['tmp_name']);
							}
							else
							{
								$ext				= substr(strrchr($_FILES['upfile']['name'], "."), 1);							
								$save_as			= time().rand(10000,1000000).".".$ext;
								$save_as			= mysqli_real_escape_string($conn, $save_as);
								$upload_path		= PUBLIC_HTML_PATH."/uploads/".$save_as;
								$resized_path 		= $upload_path;
								
								// upload file
								move_uploaded_file($_FILES['upfile']['tmp_name'], $upload_path);
								
								smart_mysql_query("UPDATE exchangerix_exchanges SET proof='$save_as' WHERE exchange_id='$exchange_id' LIMIT 1");
							}
						}
				}
				///////////////
				
				// ex_from_rate ex_to_rate //client_details // status
				$recipient = $row['client_details']. "<".$row['client_email'].">";
				SendEmail($recipient, "Thank for your exchange - ".SITE_TITLE, "Hi,<br><br>Thank you for your exchange.<br><br>ID: <b>".$row['reference_id']."</b><br>Send Amount: <b>".floatval($row['exchange_amount'])."</b> ".$row['from_currency']."<br>Receive Amount: <b>".floatval($row['receive_amount'])."</b> ".$row['to_currency']."<br>To account: <b>".$row['to_account']."</b><br>Date: ".$row['payment_date']."<br><br>You can track your exchange: <a href='".SITE_URL."track_order.php?id=".$row['reference_id']."' target='_blank'>track exchange</a><br><br>Thank you for working with us!");			
				
				
				header ("Location: payment_success.php?manual=1");
				exit();
			}
			else
			{
				$allerrors = "";
				foreach ($errs as $errorname)
					$allerrors .= $errorname."<br/>";
			}		
	 	}
 		
	}
	else
	{
		$ptitle = "Exchange";
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE	= $ptitle;

	$bg_dark = 1;
	require_once ("inc/header.inc.php");

?>	

	<?php

		if ($total > 0) {

	?>

	<div class="row">
		<div class="col-md-8">

			<div class="widget" id="expage">
			
				<h1 class="text-center">
					<span class="hidden-xs" style="margin-right: 10px">Exchange</span>
					<?php if ($send_row['image'] != "no_image.png") { ?>
						<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $send_row['image']; ?>" width="35" height="35" class="imgrs" />
					<?php } ?> 
					<?php echo $send_row['currency_name']." ".$send_row['currency_code']; ?>
					 <i class="fa fa-long-arrow-right" aria-hidden="true"></i> 
					<?php if ($receive_row['image'] != "no_image.png") { ?>
					 	<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $receive_row['image']; ?>" width="35" height="35" class="imgrs" />
					<?php } ?> 
					 <?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>					 
				</h1>
	
				<div class="wrap">
				  <div class="links">
				    <div class="dot done">STEP 1</div>
				    <div class="dot done">STEP 2</div>
				    <div class="dot current">STEP 3</div>
				  </div>
				</div>		
			
				<h2 class="lined text-center">Exchange Confirmation</h2>
				
				<div class="row">
				<div class="col-md-10 col-md-offset-1">
					
				<?php if ($row['is_manual'] == 1) { ?>
					<div class="well">
						<h4><i class="fa fa-hand-o-right fa-lg" aria-hidden="true"></i> Manual Exchange</h4>
						This is manual exchange. Operator will need some time to review your payment.
						<?php if (SHOW_OPERATOR_HOURS == 1) { ?><br>Working hours: <?php echo OPERATOR_HOURS; ?> <?php echo OPERATOR_TIMEZONE; ?><?php } ?>
					</div>						
				<?php } ?>					

				<?php if (isset($allerrors)) { ?>
					<div class="alert alert-danger"><?php echo $allerrors; ?></div>
				<?php } ?>
				
				<h3>Account Receive Details</h3>
				<input type="text" class="form-control" value="<?php echo $row['to_account']; ?>" disabled="disabled" style="background: #F8F8F8; color: #000" />
				<br>
		
				<?php if ($direction_row['is_manual'] >= 0) { ?>
					<h3>Our <?php echo $send_row['currency_name']; //$send_row['currency_code']; ?> Account Details</h3>
					<p>Please make payment to our account and insert your payment details in the field below.</p>
					<?php
							$iquery = "SELECT * FROM exchangerix_gateways WHERE gateway_id='".(int)$send_row['gateway_id']."' AND status='active' LIMIT 1";
							$iresult = smart_mysql_query($iquery);
							if (mysqli_num_rows($iresult) > 0)
							{
								$irow = mysqli_fetch_array($iresult);
							}					
					?>
						<h3 style="color: #79b45b" class="text-center"><?php echo $irow['account_id']; ?></h3>
						<?php if ($send_row['user_instructions'] != "") { ?><div class="well"><?php echo $row['user_instructions']; ?></div><?php } ?>
				<?php } ?>
				
				<?php if ($direction_row['is_manual'] >=0 && strtolower($send_row['currency_name']) == "bitcoin") { //DEV == 0 ?>
					<p class="text-center">
						Scan QR-code to make payment or send manually <b style="font-size: 19px"><?php echo floatval($row['exchange_amount']); ?></b> <?php echo substr($row['from_currency'], -4); ?> to our wallet.<br>
						<center><img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=bitcoin:<?php echo $irow['account_id']; ?>?&amount=<?php echo floatval($row['exchange_amount']); ?>" class="img-responsive"></center>
					</p>		
				<?php } ?>
				
				<div style="background: #e6edf2; border-radius: 7px; margin-bottom: 10px; padding: 10px;" class="text-center"><h4><i class="fa fa-spinner fa-spin" style="font-size:30px; vertical-align: middle"></i> waiting for payment</h4></div><br>	

				<form action="" method="post" <?php if (PAYMENT_PROOF == 1) { ?>enctype="multipart/form-data"<?php } ?>>				
				<div class="form-group">
					<label><h3>Your Payment Details <span class="req">*</span></h3></label>
					<p class="pull-right hidden-xs" style="padding-top: 10px;"><i class="fa fa-question-circle"></i><small> have a problem with payment? <a href="<?php SITE_URL; ?>contact.php?ref=<?php echo $row['reference_id']; ?>" target="_blank">contact us</a></small></p>						
					<textarea class="form-control" rows="5" name="payment_details" placeholder="Enter your payment details (payment ID, sender details, etc)." required><?php echo getPostParameter('payment_details'); ?></textarea>
				</div>
				<?php if (PAYMENT_PROOF == 1) { ?>
				<div class="form-group row">
		          <div class="col-xs-6">
		           <label><h3><i class="fa fa-paperclip"></i> Payment Proof: <i class="fa fa-question-circle itooltip" title="you can upload any image with your payment proof, such as payment screenshot, etc" style="font-size: 14px"></i></h3></label>
		           <input type="file" name="upfile" class="form-control" accept="image/*" />
		          </div>
		          <div class="col-xs-6">
		          </div>
				</div>
				<?php } ?>
				
					<hr>
					<input type="hidden" name="action" value="confirm" />
					<input type="hidden" name="currency_send" value="<?php echo @$from_id; ?>" />
					<input type="hidden" name="currency_receive" value="<?php echo @$to_id; ?>" />
						
				<p class="text-center">
					<button type="submit" name="cancel" class="btn btn-danger btn-lg" href="#" onclick="if (confirm('Are you sure you really want to cancel your exchange?') )location.href='?action=cancel';"><i class="fa fa-times" aria-hidden="true"></i> Cancel</button>&nbsp;
					<button type="submit" id="proceed" name="proceed" class="btn btn-success btn-lg"><i class="fa fa-check" aria-hidden="true"></i> Confirm</button>
				</p>
				</form>
				
				</div>
				</div>
			
			</div><!-- end widget -->

		</div>
		<div class="col-md-4">
			
			<div class="widget" id="expage_details">
				<h1><i class="fa fa-refresh" aria-hidden="true"></i> Your Exchange</h1>
				<table class="table table-striped table-bordered">
				<tr>
					<td width="45%">Exchange ID:</td>
					<td><b><?php echo $row['reference_id']; ?></b></td>
				</tr>					
				<tr>
					<td><i class="fa fa-arrow-up" aria-hidden="true" style="color: #8dc6fb"></i> Amount Send:</td>
					<td><b><?php echo floatval($row['exchange_amount']); ?></b> <?php echo substr($row['from_currency'], -4); ?></td>
				</tr>	
				<tr>
					<td><i class="fa fa-arrow-down" aria-hidden="true" style="color: #5cb85c"></i> Amount Receive:</td>
					<td><b><?php echo floatval($row['receive_amount']); ?></b> <?php echo substr($row['to_currency'], -4); ?></td>
				</tr>
				<tr>
					<td>Exchange Rate:</td>
					<td><?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?></td>
				</tr>
				<?php if ($row['exchange_fee'] != "" && $row['exchange_fee'] != "0.0000") { ?>		
				<tr>
					<td>Exchange Fee:</td>
					<td><?php echo floatval($row['exchange_fee']); ?> <?php echo $row['from_currency']; ?></td>
				</tr>
				<?php } ?>
				<?php if (isLoggedIn() && $row['discount'] > 0) { ?>		
				<tr>
					<td>Discount:</td>
					<td><b style="color:#3ebf10"><?php $discount = ($row['discount']/100) * $row['exchange_amount']; echo $discount ?> <?php echo $row['from_currency']; ?></b> (<?php echo $row['discount']; ?>%)</td>
				</tr>
				<?php } ?>				
				<tr>					
					<td>Exchange Date:</td>
					<td><?php echo $row['payment_date']; ?></td>
				</tr>
				<tr style="background: #EEE">					
					<td><h3 class="total_pay">Total for pay:</h3></td>
					<td><h3 class="total_pay"><?php echo floatval($row['exchange_amount']+$row['exchange_fee']); ?> <?php echo substr($row['from_currency'], -4); ?></h3></td>
				</tr>																		
				</table>
			</div>
		
			
			<div class="widget">
				<b><i class="fa fa-lock fa-lg" aria-hidden="true"></i> Secure Exchange</b><br>
				Your exchange is always safe and secure.
				
				<?php if (RESERVE_MINUTES > 0) { ?>
					<br><br><b>Exchange amount is reserved for <?php echo (int)RESERVE_MINUTES; ?> minutes.</b>
					<br>Please complete your exchange during this time.

					<center>
					<div class="countdown">
						<i class="fa fa-clock-o fa-2x"></i> <span id="clock" style="margin-left: 5px; font-size: 30px;"></span>
					</div>
					</center>
					
					<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
					<script type="text/javascript" src="<?php echo SITE_URL; ?>js/countdown/countdown.js"></script>
					<script type="text/javascript" src="<?php echo SITE_URL; ?>js/countdown/moment.js"></script>
					<script type="text/javascript" src="<?php echo SITE_URL; ?>js/countdown/moment-timezone-with-data.js"></script>					
					<script type="text/javascript">						
					<!--
					var countddown = moment.tz("<?php echo $row['countdate']; ?>", "<?php echo SITE_TIMEZONE; ?>"); //America/New_York
					$('#clock').countdown(countddown.toDate())
					.on('update.countdown', function(event) {
					  var $this = $(this);
					  $this.html(event.strftime('<span>%M:%S</span>'));
					})
					.on('finish.countdown', function(event) {
					  $(this).html('<div class="alert alert-danger"><h3 style="color: #c15555">Exchange time has expired!</h3></div>')
					    .parent().addClass('disabled');
					    $('#proceed').addClass('disabled');
					    $('#expage').addClass('disabledbox');
					    $('#expage_details').addClass('disabledbox');
					    //$("div.content_wrapper").animate({width: "toggle"});
					    //$("div.content_wrapper, div.content").hide();
					    //$("div.content_wrapper, div." + content).show();//
					});
					-->
					</script>					
					
				<?php } ?>
			</div>			

			<p><span style="color: #999"><small>Note: for security reasons, your IP (<?php echo @$user_ip; ?>) was recorded by our system.</small></span></p>
					
		</div>
	</div>


	<?php }else{ ?>
		<h1>Exchange</h1>
		<div class="alert alert-warning">Sorry, no transaction found.</div>
		<p align="center"><a class="btn btn-primary" href="<?php echo SITE_URL; ?>"><?php echo CBE1_GO_BACK; ?></a></p>
	<?php } ?>		  	
	
	

<?php require_once ("inc/footer.inc.php"); ?>