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

	if (isset($_GET['id']) && is_numeric($_GET['id'])) // make 232BJSnmsSdh73Wdjj21
	{
		$reference_id = (int)$_GET['id'];
	}else if (isset($_POST['id']) && is_numeric($_POST['id'])) // make 232BJSnmsSdh73Wdjj21
	{
		$reference_id = (int)$_POST['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}	
	
	
	if (isset($_POST['action']) && $_POST['action'] == 'contact')
	{
		unset($errs);
		$errs = array();

		$fname		= getPostParameter('fname');
		$email		= getPostParameter('email');
		$phone		= getPostParameter('phone');
		$exid		= trim(getPostParameter('exid'));
		$subject	= "Contact regards Exchange #".$exid;
		$umessage	= nl2br(getPostParameter('umessage'));	

		if (!($fname && $exid && $email && $umessage))
		{
			$errs[] = CBE1_CONTACT_ERR1;
		}
		else
		{
			if (isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				$errs[] = CBE1_CONTACT_ERR2;
			}
		}

		if (count($errs) == 0)
		{
			$umessage .= "<br>------------------------------------";
			if ($exid != "") $umessage .= "<br><b>Exchange ID</b>: ".$exid;
			if (isLoggedIn()) $umessage .= "<br><b>User ID</b>: ".(int)$_SESSION['userid'];
			if ($phone != "") $umessage .= "<br><b>Phone</b>: ".$phone;
			
			$from = 'From: '.$fname.' <'.$email.'>';
			SendEmail(SITE_MAIL, $subject, $umessage, $noreply_mail = 1, $from);
				
			header("Location: track_order.php?id=".$reference_id."&msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>\n";
		}
	}
	
		
	
	if (RESERVE_MINUTES > 0) $a_sql = " DATE_ADD(created, INTERVAL ".(int)RESERVE_MINUTES." MINUTE) AS countdate, "; else $a_sql = "";

	$query = "SELECT *, TIMESTAMPDIFF(MINUTE, created, now()) as time_ago, $a_sql DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS payment_date FROM exchangerix_exchanges WHERE reference_id='$reference_id' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);
	
	// if created more than 7 days // hide other data


	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
		
		if (RESERVE_MINUTES > 0 && $row['time_ago'] >= RESERVE_MINUTES)
		{
			smart_mysql_query("UPDATE exchangerix_exchanges SET status='timeout' WHERE exchange_id='$exchange_id' LIMIT 1");
			//header("Location: payment_declined.php?reason=timeout");
			//exit();
		}	
		
		$send_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='".(int)$row['from_currency_id']."' LIMIT 1"));
		$receive_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='".(int)$row['to_currency_id']."' LIMIT 1"));
		$direction_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_exdirections WHERE from_currency='".(int)$row['from_currency_id']."' AND to_currency='".(int)$row['to_currency_id']."' AND from_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_send='1' AND (reserve>0 || reserve='') AND status='active') AND to_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_receive='1' AND (reserve>0 || reserve='') AND status='active') AND status='active' LIMIT 1"));	
		
		$ptitle	= "Exchange ".$send_row['currency_name']." ".$send_row['currency_code']." to ".$receive_row['currency_name']." ".$receive_row['currency_code'];
		
		
		// cancel exchange 
		if (isset($_GET['action']) && $_GET['action'] == "cancel" && isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid'])
		{
			if ($row['proof'] != "") { if (file_exists(PUBLIC_HTML_PATH."/uploads/".$row['proof'])) @unlink(PUBLIC_HTML_PATH."/uploads/".$row['proof']); }
						
			smart_mysql_query("UPDATE exchangerix_exchanges SET status='cancelled', updated=NOW() WHERE exchange_id='$exchange_id' LIMIT 1");
			unset($_SESSION['transaction_id']);
			
			header ("Location: index.php");
			exit();
		}
		
		//// ADD TESTIMONIAL //////////////////////////////////////////////
		if (isset($_POST['action']) && $_POST['action'] == "addreview")
		{
			$userid			= (int)$_SESSION['userid'];
			
			$client			= ucwords(strtolower($row['client_details']));
			$client_arr		= explode(' ',trim($client));			
			$author 		= mysqli_real_escape_string($conn, $client_arr[0]);
			
			$rating			= (int)getPostParameter('rating');
			$review_title	= mysqli_real_escape_string($conn, getPostParameter('review_title'));
			$review			= mysqli_real_escape_string($conn, nl2br(trim(getPostParameter('review'))));
			$review			= ucfirst(strtolower($review));

			unset($errs);
			$errs = array();

			if (!($rating && $review_title && $review))
			{
				$errs[] = CBE1_REVIEW_ERR;
			}
			else
			{
				$number_lines = count(explode("<br />", $review));
				
				if (strlen($review) > MAX_REVIEW_LENGTH)
					$errs[] = str_replace("%length%",MAX_REVIEW_LENGTH,CBE1_REVIEW_ERR2);
				else if ($number_lines > 5)
					$errs[] = CBE1_REVIEW_ERR3;
			}

			if (count($errs) == 0)
			{
				$review = substr($review, 0, MAX_REVIEW_LENGTH);				
				$check_review = mysqli_num_rows(smart_mysql_query("SELECT * FROM exchangerix_reviews WHERE exchange_id='".(int)$row['exchange_id']."'"));
				
				if ($check_review == 0)
				{
					(REVIEWS_APPROVE == 1) ? $status = "pending" : $status = "active";
					$review_query = "INSERT INTO exchangerix_reviews SET exchange_id='".(int)$row['exchange_id']."', rating='$rating', user_id='".(int)$row['user_id']."', author='$author', review_title='$review_title', review='$review', status='$status', added=NOW()";
					$review_result = smart_mysql_query($review_query);
					$review_added = 1;
				}
				else
				{
					$errormsg = CBE1_REVIEW_ERR5;
				}
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}
		}
		//////////////////////////////////////////////////////////////////////////////////////////		
		

 		// confirm exchange
 		/*if (isset($_POST['action']) && $_POST['action'] == "confirm" && isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid'])
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
				//smart_mysql_query("UPDATE exchangerix_currencies SET reserve=reserve+'".floatval($row['exchange_amount'])."' WHERE currency_id='".(int)$row['from_currency_id']."' LIMIT 1");
				
				header ("Location: payment_success.php?manual=1");
				exit();
			}
			else
			{
				$allerrors = "";
				foreach ($errs as $errorname)
					$allerrors .= $errorname."<br/>";
			}		
	 	}*/
 		
	}
	else
	{
		$ptitle = "Track Exchange";
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE	= $ptitle;

	$bg_dark = 1;
	require_once ("inc/header.inc.php");

?>	

	<?php

		if ($total > 0) {

	?>
	
	<?php if (isLoggedIn()) { ?>
	<div class="row">
		<div class="col-md-12 hidden-xs">
		<div id="acc_user_menu">
			<ul><?php require("inc/usermenu.inc.php"); ?></ul>
		</div>
	</div>
	<?php } ?>	
	

	<div class="row">
		<div class="col-md-8">

			<div class="widget">
			
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
			
				<h2 class="lined text-center">Exchange #<?php echo $row['reference_id']; ?></h2>
				
				<div class="row">
				<div class="col-md-10 col-md-offset-1">
					
				<?php if ($row['is_manual'] == 1 && $row['status'] == "pending") { ?>
					<div class="well">
						<h4><i class="fa fa-hand-o-right fa-lg" aria-hidden="true"></i> Manual Exchange</h4>
						This is manual exchange. Operator will need some time to review your payment.
						<?php if (SHOW_OPERATOR_HOURS == 1) { ?><br>Working hours: <?php echo OPERATOR_HOURS; ?> <?php echo OPERATOR_TIMEZONE; ?><?php } ?>
					</div>						
				<?php } ?>					

				<?php if (isset($allerrors)) { ?>
					<div class="alert alert-danger"><?php echo $allerrors; ?></div>
				<?php } ?>

				<div class="table-responsive">
				<table class="table table-striped table-bordered">
				<!--<tr>
					<td width="45%">Exchange ID:</td>
					<td><b><?php echo $row['reference_id']; ?></b></td>
				</tr>-->
				<tr>
					<td>From:</td>
					<td><?php if ($send_row['image'] != "no_image.png") { ?><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $send_row['image']; ?>" width="22" height="22" class="imgrs" /><?php } ?> <?php echo $send_row['currency_name']." ".$send_row['currency_code']; ?> </td>
				</tr>	
				<tr>
					<td>To:</td>
					<td><?php if ($receive_row['image'] != "no_image.png") { ?><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $receive_row['image']; ?>" width="22" height="22" class="imgrs" /><?php }?> <?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?></td>
				</tr>									
				<tr>
					<td><i class="fa fa-arrow-up" aria-hidden="true" style="color: #8dc6fb"></i> Send:</td>
					<td><h4 style="color: #0199de"><?php echo floatval($row['exchange_amount']); ?> <span style="color: #000"><?php echo substr($row['from_currency'], -4); ?></span></h4></td>
				</tr>	
				<tr>
					<td><i class="fa fa-arrow-down" aria-hidden="true" style="color: #5cb85c"></i> Receive:</td>
					<td><h4 style="color: #5cb85c"><?php echo floatval($row['receive_amount']); ?></b> <span style="color: #000"><?php echo substr($row['to_currency'], -4); ?></span></h4></td>
				</tr>
				<tr>
					<td>Exchange Rate:</td>
					<td><?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?></td>
				</tr>
				<?php /*if ($row['exchange_fee'] != "" && $row['exchange_fee'] != "0.0000") { ?>		
				<tr>
					<td>Exchange Fee:</td>
					<td><?php echo floatval($row['exchange_fee']); ?> <?php echo $row['from_currency']; ?></td>
				</tr>
				<?php }*/ ?>				
				<tr>
					<td>To Account:</td>
					<td style="background: #f4f7ef;">
						<?php if (isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid']) { ?>
						<a href="#"><sup class="badge itooltip pull-right" title="If you filled wrong details, then please cancel your exchange asap and contact us" style="background: #f75c5d">wrong details?</sup></a>
						<b><i class="fa fa-user-circle"></i> <?php echo $row['client_details']; ?></b><br>
						<?php echo $row['client_email']; ?><br> 
						<b><?php echo $row['to_account']; ?></b>
						<?php }else{ ?>
							<i class="fa fa-eye-slash"></i> hidden <sup class="itooltip" title="please login to see all exchange details"><i class="fa fa-info-circle"></i></sup>
						<?php } ?>
					</td>
				</tr>
				<?php if ($row['from_account'] != "") { ?>
				<?php if (isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid']) { ?>					
					<tr>					
						<td>Payment Details:</td>
						<td>
							<?php echo $row['from_account']; ?>
							<?php if ($row['proof'] != "") { ?><br> <i class="fa fa-paperclip"></i> <a href="<?php echo SITE_URL; ?>uploads/<?php echo $row['proof']; ?>" data-lightbox="image-1" data-title="Payment Proof">payment proof</a><?php } ?></td>
					</tr>
					<?php }else{ ?>
						<i class="fa fa-eye-slash"></i> hidden <sup class="itooltip" title="please login to see all exchange details"><i class="fa fa-info-circle"></i></sup>
					<?php } ?>
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
				<?php if ($row['process_date'] != "" && $row['process_date'] != "0000-00-00 00:00:00") { ?>
				<tr>					
					<td>Processed On:</td>
					<td><?php echo findTimeAgo($row['process_date']); ?></td>
				</tr>
				<?php } ?>
				<?php if ($row['status'] == "pending" || $row['status'] == "waiting") { ?>				
				<tr>					
					<td>Date Updated:</td>
					<td><?php if ($row['updated'] != "" && $row['updated'] != "0000-00-00 00:00:00") { ?><?php echo findTimeAgo($row['updated']); ?><?php }else{ ?><i class="fa fa-refresh fa-spin"></i> waiting for confirmation</i><?php } ?></td>
				</tr>
				<?php } ?>				
				<?php if ($row['status'] == "timeout") { ?>				
				<tr>					
					<td>Expired:</td>
					<td><?php echo $row['updated']; ?></td>
				</tr>
				<?php } ?>
				<?php if (isLoggedIn() && $row['ref_id'] > 0 && $row['ref_id'] == (int)$_SESSION['userid'] && $row['status'] != "cancelled" && $row['status'] != "timeout" && $row['status'] != "declined") { ?>
					<tr>					
						<td><i class="fa fa-money"></i> Your Referral Earnings:</td>
						<td><span style="font-size: 16px; color: #5cb85c"><b>$<?php echo CalculatePercentage(($row['exchange_amount']/20), REFERRAL_COMMISSION); ?></b></span></td>
					</tr>					
				<?php } ?>											
				<tr>					
					<td>Status:</td>
					<td>						
					<?php
							switch ($row['status'])
							{
								case "confirmed":	echo "<span class='label label-success'><i class='fa fa-check'></i> ".STATUS_CONFIRMED."</span>"; break;
								case "pending":		echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> ".STATUS_PENDING."</span>"; break;
								case "waiting":		echo "<span class='label label-warning'>waiting</span>"; break;
								case "cancelled":	echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
								case "timeout":		echo "<span class='label label-danger'><i class='fa fa-clock-o'></i> timeout</span>"; break;
								case "declined":	echo "<span class='label label-danger'>".STATUS_DECLINED."</span>"; break;
								case "failed":		echo "<span class='label label-danger'>".STATUS_FAILED."</span>"; break;
								case "paid":		echo "<span class='label label-success'>".STATUS_PAID."</span>"; break;
								default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}

							if ($row['status'] == "declined" && $row['reason'] != "")
							{
								echo " <span class='exchangerix itooltip' title='".$row['reason']."'><i class='fa fa-info-circle' style='color: red'></i></span>";
							}
					?>						
					</td>
				</tr>								
				<!--
				<tr style="background: #EEE">					
					<td><h3>Total for pay:</h3></td>
					<td><h3><?php echo floatval($row['exchange_amount']+$row['exchange_fee']); ?> <?php echo substr($row['from_currency'], -4); ?></h3></td>
				</tr>
				-->
				</table>
				</div>																				
				
				
				<?php if (isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid']) { ?>
					<form action="" method="post" <?php if (PAYMENT_PROOF == 1 && $row['proof'] == "") { ?>enctype="multipart/form-data"<?php } ?>>
					<?php if (PAYMENT_PROOF == 1 && $row['proof'] == "") { ?>
					<div class="form-group">
						<label><i class="fa fa-paperclip"></i> Upload Payment Proof:</label>
						<input type="file" name="upfile" class="form-control" accept="image/*" />
					</div>
					<?php } ?>
	
					<!--<input type="hidden" name="action" value="confirm" />-->
				
					<?php if ($row['status'] == "pending" || $row['status'] == "waiting") { ?>
					<p class="text-center">
						<button class="btn btn-danger btn-lg" href="#" onclick="if (confirm('Are you sure you really want to cancel your exchange?') )location.href='<?php echo SITE_URL; ?>track_order.php?id=<?php echo $row['reference_id']; ?>&action=cancel';"><i class="fa fa-times" aria-hidden="true"></i> Cancel Exchange</button>&nbsp;
						<!--<button type="submit" id="proceed" name="proceed" class="btn btn-success btn-lg"><i class="fa fa-check" aria-hidden="true"></i> I have paid</button>-->
					</p>
					</form>
					<?php } ?>
					
				<?php } ?> 
				
				</div>
				</div>
				
				<p class="text-center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><i class="fa fa-angle-left" aria-hidden="true"></i> Go Back</a></p>
			
			</div><!-- end widget -->

		</div>
		<div class="col-md-4">		
			
				<?php if ($review_added == 1) { ?>
					<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo CBE1_REVIEW_SENT; ?></div>
				<?php } ?>	

					<?php
							$check_review = mysqli_num_rows(smart_mysql_query("SELECT * FROM exchangerix_reviews WHERE exchange_id='".$row['exchange_id']."'"));
							if ($row['status'] == "confirmed" && $check_review == 0) {			
						
					?>
						<div class="widget" style="background: #f2f9fc">
							
							<form action="" method="post">
							<h1><i class="fa fa-comments-o"></i> Leave your feedback</h1>
							<p>Your feedback is greatly appreciated. Please let us know your opinion about our service.</p>
							
								<?php if (isset($errormsg) && $errormsg != "") { ?>
									<div class="alert alert-danger"><?php echo $errormsg; ?></div>
								<?php } ?>
				
								
								<div class="form-group">
								<select name="rating" class="form-control" required>
									<option value=""><?php echo CBE1_REVIEW_RATING_SELECT; ?></option>
									<option value="5" <?php if (@$rating == 5) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING1; ?></option>
									<option value="4" <?php if (@$rating == 4) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING2; ?></option>
									<option value="3" <?php if (@$rating == 3) echo "selected"; ?>>&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING3; ?></option>
									<option value="2" <?php if (@$rating == 2) echo "selected"; ?>>&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING4; ?></option>
									<option value="1" <?php if (@$rating == 1) echo "selected"; ?>>&#9733; - <?php echo CBE1_REVIEW_RATING5; ?></option>
								</select>
								</div>
								<div class="form-group">
									<input type="text" name="review_title" id="review_title" placeholder="<?php echo CBE1_REVIEW_RTITLE; ?>" value="<?php echo getPostParameter('review_title'); ?>" size="47" class="form-control" required /></div>																	
								<div class="form-group">
									<textarea name="review" rows="5" class="form-control" placeholder="<?php echo CBE1_REVIEW_REVIEW; ?>" required><?php echo getPostParameter('review'); ?></textarea></h3>
								</div>
								<input type="hidden" name="action" value="addreview" />
								<button type="submit" class="btn btn-info">Submit Feedback</button>
							</form>
						</div>
					<?php } ?>	

			
			<div class="widget">
				
				<?php if ($row['status'] != "confirmed") { ?>
					<p class="pull-right"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><i class="fa fa-angle-left" aria-hidden="true"></i> Go Back</a></p>
				<?php } ?>
				
				<h1><i class="fa fa-question-circle" aria-hidden="true"></i> Need help?</h1>
	
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>	
				
			<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
				<div class="alert alert-success"><i class="fa fa-check-circle fa-lg"></i> <?php echo CBE1_CONTACT_SENT; ?></div>
			<?php }else{ ?>

			<?php if (OPERATOR_STATUS == "online") { ?>
				<p>If you have any issues with your payment or exchange, please feel free to contact us.</p>
				<p class="text-center"><i class="fa fa-headphones fa-2x icircle" aria-hidden="true" style="color: #79b45b; padding-right: 7px; "></i> Operator: <span class="label label-success"><i id="operator_live2" class="fa fa-circle" aria-hidden="true" style="color: #8bf24a"></i> online</span></p>
								
				<p>Our operators are online now, please use our contact details from the top of page.</p>
	       <?php }else{ ?>
	        	<p class="text-center"><i class="fa fa-users fa-2x icircle" aria-hidden="true" style="color: #777777;"></i> All our operators are currently <span class="label label-default"> offline</span></p>
	       		
	       		<?php if (isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid']) { ?>
	       		<p class="text-center">So if you have any problem or issues with your exchange please use form below to contact us.</p>
				  <form action="" method="post">
					<div class="form-group">
						<label>Exchange ID:</label>
						<input class="form-control" type="text" value="<?php echo $row['reference_id']; ?>" readonly>
					</div>
					<?php if (isLoggedIn() && $row['user_id'] == (int)$_SESSION['userid']) { ?>
						<?php
								$urow = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_id='".(int)$_SESSION['userid']."' AND status='active' LIMIT 1"));						
						?>				  
				  	<div class="form-group">
					  <label><?php echo CBE1_CONTACT_NAME; ?> <span class="req">* </span></label>
					  <input name="fname" class="form-control" type="text" value="<?php echo $urow['fname']." ".$urow['lname']; ?>" required="required">
					</div>
					<div class="form-group">
					  <label><?php echo CBE1_CONTACT_EMAIL; ?> <span class="req">* </span></label>
					  <input name="email" class="form-control" type="email" value="<?php echo $urow['email']; ?>" required="required">
					</div>
					<div class="form-group">
					  <label><i class="fa fa-phone"></i> Phone</label>
					  <input name="phone" class="form-control" type="text" value="<?php echo ($urow['phone'] != "") ? $urow['phone'] : getPostParameter('phone'); ?>" required="required">
					</div>					
					<?php }else{ ?>
				  	<div class="form-group">
					  <label><?php echo CBE1_CONTACT_NAME; ?> <span class="req">* </span></label>
					  <input name="fname" class="form-control" type="text" value="<?php echo getPostParameter('fname'); ?>" required="required">
					</div>
					<div class="form-group">
					  <label><?php echo CBE1_CONTACT_EMAIL; ?> <span class="req">* </span></label>
					  <input name="email" class="form-control" type="email" value="<?php echo getPostParameter('email'); ?>" required="required">
					</div>
					<div class="form-group">
					  <label><i class="fa fa-phone"></i> Phone</label>
					  <input name="phone" class="form-control" type="text" value="<?php echo getPostParameter('phone'); ?>" required="required">
					</div>									
					<?php } ?>
					<!--
					<div class="form-group">
					  <label><?php echo CBE1_CONTACT_SUBJECT; ?></label>
					  <input name="subject" class="form-control" type="text" value="<?php echo getPostParameter('subject'); ?>" required="required">
					</div>
					-->
					<div class="form-group">
					  <label><?php echo CBE1_CONTACT_MESSAGE; ?></label>
					  <textarea cols="50" rows="5" class="form-control" required="required" name="umessage" placeholder="please describe your problem about this exchange..."><?php echo getPostParameter('umessage'); ?></textarea>
					</div>
					<input type="hidden" name="exid" id="exid" value="<?php echo $row['reference_id']; ?>" />
					<input type="hidden" name="action" id="action" value="contact" />
					<button type="submit" class="btn btn-success btn-lg" name="Submit"><?php echo CBE1_CONTACT_BUTTON; ?></button>
				  </form>
			  <?php }else{ ?>
			  	Please feel free to <a href="<?php echo SITE_URL; ?>contact.php?ref=<?php echo $row['reference_id']; ?>">contact us</a> and send message. We will reply to you shortly.
			  <?php } ?>	
		  
	       <?php } ?>
	       
	       <?php } // sent end ?>			
				
				
			</div>
					
		</div>
	</div>


	<?php }else{ ?>
		<h1>Exchange</h1>
		<div class="alert alert-warning">Sorry, no transaction found.</div>
		<p align="center"><a class="btn btn-primary" href="<?php echo SITE_URL; ?>"><?php echo CBE1_GO_BACK; ?></a></p>
	<?php } ?>		  	
	
	

<?php require_once ("inc/footer.inc.php"); ?>