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
	

			// verification progress
			$e = 0;
			$verification_progress = 0;			
			if (EMAIL_VERIFICATION == 1) $e+=1;
			if (PHONE_VERIFICATION == 1) $e+=1;
			if (DOCUMENT_VERIFICATION == 1) $e+=1;
			if (ADDRESS_VERIFICATION == 1) $e+=1;
			if ($e > 0) $progress_percent = floor(100/$e);

	
	function CheckIfVerified($userid, $type)
	{
		switch ($type)
		{
			case "email": 		$result = smart_mysql_query("SELECT verified_email AS is_verified FROM exchangerix_users WHERE user_id='".(int)$userid."' LIMIT 1"); break;
			case "phone": 		$result = smart_mysql_query("SELECT verified_phone AS is_verified FROM exchangerix_users WHERE user_id='".(int)$userid."' LIMIT 1"); break;
			case "document": 	$result = smart_mysql_query("SELECT verified_document AS is_verified FROM exchangerix_users WHERE user_id='".(int)$userid."' LIMIT 1"); break;
			case "address": 	$result = smart_mysql_query("SELECT verified_address AS is_verified FROM exchangerix_users WHERE user_id='".(int)$userid."' LIMIT 1"); break;
		}
		
		$row = mysqli_fetch_array($result);
		if ($row['is_verified'] == 1)
			return true;
		else
			return false;
	}	
	
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
	
	
	if (isset($_POST['action']) && $_POST['action'] == "upload_document")
	{
		unset($errs);
		$errs = array();
		
		$target_dir = PUBLIC_HTML_PATH."/uploads/";
		
		if (ALLOWED_FILES != "") $files_ext = ALLOWED_FILES; else $files_ext = "jpg|png|jpeg";
		if (FILES_MAX_SIZE != "" && is_numeric(FILES_MAX_SIZE)) $files_size = FILES_MAX_SIZE; else $files_size = 5242880; // 5MB
		
		$files_size_kb 		= round($files_size/1024);
		$files_ext_list 	= strtoupper(str_replace("|", ", ", $files_ext));
		$target_dir 		= PUBLIC_HTML_PATH."/uploads/";


		if ($_FILES['upfile']['tmp_name'])
		{
			if (is_uploaded_file($_FILES['upfile']['tmp_name']))
			{
				list($width, $height, $type) = getimagesize($_FILES['upfile']['tmp_name']);
				
				if ($_FILES['upfile']['size'] > $files_size)
				{
					// Sorry, your file is too large.
					$errs[] = "The image file size is too big. It exceeds $files_size_kb Kb";
				}
				elseif (preg_match('/\\.('.$files_ext.')$/i', $_FILES['upfile']['name']) != 1)
				{
					$errs[] = "Sorry, only ".$files_ext_list." files are allowed";
					unlink($_FILES['upfile']['tmp_name']);
				}
				else
				{
					$ext 			= substr(strrchr($_FILES['upfile']['name'], "."), 1);
					$save_as 		= random_filename(50).".".$ext; //basename($_FILES["upfile"]["name"])					
					$upload_path 	= $target_dir.$save_as;
					
					if (file_exists($upload_path))
						$errs[] = "Sorry, file already exists";					
				}
			}
		}
		else
		{
			$errs[] = "Please select image file";
		}

		if (count($errs) > 0)
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
		else
		{
		    if (move_uploaded_file($_FILES['upfile']['tmp_name'], $upload_path))
			{
		        smart_mysql_query("UPDATE exchangerix_users SET verified_document='$save_as' WHERE user_id='$userid' AND verified_document!='1' LIMIT 1");
				
				header("Location: myaccount.php?msg=3#verification");
				exit();
		    }
		    else
		    {
		        echo "Sorry, there was an error uploading your file";
		    }
		}
	}
	
	
	if (isset($_POST['action']) && $_POST['action'] == "upload_address")
	{
		unset($errs);
		$errs = array();
	
		if (ALLOWED_FILES != "") $files_ext = ALLOWED_FILES; else $files_ext = "jpg|png|jpeg";
		if (FILES_MAX_SIZE != "" && is_numeric(FILES_MAX_SIZE)) $files_size = FILES_MAX_SIZE; else $files_size = 5242880; // 5MB
		
		$files_size_kb 		= round($files_size/1024);
		$files_ext_list 	= strtoupper(str_replace("|", ", ", $files_ext));
		$target_dir 		= PUBLIC_HTML_PATH."/uploads/";


		if ($_FILES['upfile']['tmp_name'])
		{
			if (is_uploaded_file($_FILES['upfile']['tmp_name']))
			{
				list($width, $height, $type) = getimagesize($_FILES['upfile']['tmp_name']);
				
				if ($_FILES['upfile']['size'] > $files_size)
				{
					// Sorry, your file is too large.
					$errs[] = "The image file size is too big. It exceeds $files_size_kb Kb";
				}
				elseif (preg_match('/\\.('.$files_ext.')$/i', $_FILES['upfile']['name']) != 1)
				{
					$errs[] = "Sorry, only ".$files_ext_list." files are allowed";
					unlink($_FILES['upfile']['tmp_name']);
				}
				else
				{
					$ext 			= substr(strrchr($_FILES['upfile']['name'], "."), 1);
					$save_as 		= random_filename(50).".".$ext; //basename($_FILES["upfile"]["name"])					
					$upload_path 	= $target_dir.$save_as;
					
					if (file_exists($upload_path))
						$errs[] = "Sorry, file already exists";					
				}
			}
		}
		else
		{
			$errs[] = "Please select image file";
		}


		if (count($errs) > 0)
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
		else
		{
		    if (move_uploaded_file($_FILES['upfile']['tmp_name'], $upload_path))
			{
		        smart_mysql_query("UPDATE exchangerix_users SET verified_address='$save_as' WHERE user_id='$userid' AND verified_address!='1' LIMIT 1");
				header("Location: myaccount.php?msg=4#verification");
				exit();
		    }
		    else
		    {
		        echo "Sorry, there was an error uploading your file";
		    }
		}
	}	
	

	if (isset($_POST['action']) && $_POST['action'] == "send_sms")
	{
		$sms_code = mt_rand(100000,900000).mt_rand(10,99);

		if (count($errs) == 0 && $row['phone'] != "" && SMS_API_KEY != "" && SMS_API_SECRET != "")
		{
			$up_query = "UPDATE exchangerix_users SET sms_code='$sms_code' WHERE user_id='$userid' AND verified_phone='0' AND sms_code='' LIMIT 1";

			// sending sms //
			require_once("inc/sms/nexmo/NexmoMessage.php");
			$sms 			= new NexmoMessage(SMS_API_KEY, SMS_API_SECRET);
			$sms_number 	= $row['phone']; //'+447234567890'
			$sms_message 	= SITE_TITLE.' code: '.$sms_code;
			$sms->sendText($sms_number, 'MyApp', $sms_message);
			
			/*
		     $receipt = new NexmoReceipt();
		     if ($receipt->exists()) {
		         switch ($receipt->status) {
		             case $receipt::STATUS_DELIVERED:
		                 // The message was delivered to the handset!
		                 break;
		             
		             case $receipt::STATUS_FAILED:
		             case $receipt::STATUS_EXPIRED:
		                 // The message failed to be delivered
		                 break;
		         }
		     }
		    */						
			
			if (smart_mysql_query($up_query))
			{
				header("Location: myaccount.php?msg=1#verification");
				exit();
			}
		}
	}
	

	if (isset($_POST['action']) && $_POST['action'] == "verify_phone")
	{
		//$sms_code		= mysqli_real_escape_string($conn, strtolower(getPostParameter('sms_code')));
		$sms_code		= (int)getPostParameter('sms_code');
		
		unset($errs);
		$errs = array();

		if(!$sms_code)
		{
			$errs[] = "Please enter verification code from your SMS";
		}
		else
		{
			$c_result = smart_mysql_query("SELECT * FROM exchangerix_users WHERE sms_code='$sms_code' AND user_id='$userid' AND verified_phone='0' LIMIT 1");
			if (mysqli_num_rows($c_result) == 0)
			{
				$errs[] = "Wrong verification code";
			}
		}
		// if did not receive //resend //dev

		if (count($errs) == 0)
		{
			$verification_progress+=$progress_percent;
			
			smart_mysql_query("UPDATE exchangerix_users SET verified_phone='1', verification_progress='$verification_progress', sms_code='' WHERE user_id='$userid' LIMIT 1");
			
			header("Location: myaccount.php?msg=2#verification");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}		
	}	
				

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_ACCOUNT_TITLE;

	require_once ("inc/header.inc.php");

?>

	<div class="row">
		<div class="col-md-12 hidden-xs">
		<div id="acc_user_menu">
			<ul><?php require("inc/usermenu.inc.php"); ?></ul>
		</div>
	</div>

	<h1><!--<i class="fa fa-user-circle"></i>--> <?php echo CBE1_ACCOUNT_TITLE; ?></h1>

	<?php if (isset($_GET['msg']) && $_GET['msg'] == "welcome") { ?>
		<div class="alert alert-success alert-dismissible fade in">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<?php echo CBE1_ACCOUNT_MSG; ?>
		</div>
	<?php } ?>

	
	<p><?php echo str_replace("%username%",$_SESSION['FirstName'],CBE1_ACCOUNT_WELCOME); ?></p>

	
	<div class="row">
	<div class="col-md-6">
		
		<br>
		<h2 class="pull-left"><i class="fa fa-refresh fa-spin" style="color: #5bbc2e"></i> Latest 10 Exchanges</h2>

     <?php

		$cc = 0;

		$equery = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT."') AS date_created, DATE_FORMAT(updated, '".DATE_FORMAT."') AS updated_date FROM exchangerix_exchanges WHERE user_id='$userid' AND status!='unknown' ORDER BY created DESC LIMIT 10";
		$eresult = smart_mysql_query($equery);
		$etotal = mysqli_num_rows($eresult);

     ?>
     		<?php if ($etotal > 0 ) { //>10 ?><a class="pull-right" href="<?php echo SITE_URL; ?>mybalance.php#exchanges" style="padding-top: 20px; color: #000">view all &rsaquo;</a><?php } ?>
	
	 		<div style="clear: both"></div>	
			<div class="table-responsive">
            <table align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><?php echo CBE1_BALANCE_DATE; ?></th>
				<th width="15%" nowrap><?php echo CBE1_PAYMENTS_ID; ?> <sup class="itooltip" title="use Reference ID to contact us with any questions">?</sup></th>
				<th width="30%" nowrap>Send <i class="fa fa-arrow-right" aria-hidden="true" style="color: #8dc6fb"></i></th>
                <th width="30%" nowrap><i class="fa fa-arrow-left" aria-hidden="true" style="color: #5cb85c"></i> Receive</th>
                <th width="13%"><?php echo CBE1_BALANCE_STATUS; ?></th>
              </tr>
			<?php if ($etotal > 0) { ?>
			<?php while ($erow = mysqli_fetch_array($eresult)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="center" nowrap><?php echo $erow['date_created']; ?></td>
				  <td valign="middle" align="center"><a href="<?php echo SITE_URL; ?>track_order.php?id=<?php echo $erow['reference_id']; ?>"><?php echo $erow['reference_id']; ?></a></td>
                  <td valign="middle" align="left" style="padding-left: 8px" nowrap><?php echo GetCurrencyImg($erow['from_currency_id'], $width=20); ?> <b><?php echo number_format($erow['exchange_amount'], 2, '.', ''); ?></b> <?php echo substr($erow['from_currency'], -4); ?></td>
                  <td valign="middle" align="left" style="padding-left: 8px" nowrap> <?php echo GetCurrencyImg($erow['to_currency_id'], $width=20); ?> <b><?php echo number_format($erow['receive_amount'], 2, '.', ''); ?></b> <?php echo substr($erow['to_currency'], -4); ?></td>
                  <td valign="middle" align="left" style="padding: 0 5px;" nowrap>
					<?php
							switch ($erow['status'])
							{
								case "confirmed":	echo "<span class='label label-success'>".STATUS_CONFIRMED."</span>"; break;
								case "pending":		echo "<span class='label label-warning'>".STATUS_PENDING."</span>"; break;
								case "waiting":		echo "<span class='label label-warning'>waiting</span>"; break;
								case "cancelled":	echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
								case "timeout":		echo "<span class='label label-danger'><i class='fa fa-clock-o'></i> timeout</span>"; break;
								case "declined":	echo "<span class='label label-danger'>".STATUS_DECLINED."</span>"; break;
								case "failed":		echo "<span class='label label-danger'>".STATUS_FAILED."</span>"; break;
								case "request":		echo "<span class='label label-default'>".STATUS_REQUEST."</span>"; break;
								case "paid":		echo "<span class='label label-success'>".STATUS_PAID."</span>"; break;
								default: echo "<span class='label label-default'>".$erow['status']."</span>"; break;
							}

							if ($erow['status'] == "declined" && $erow['reason'] != "")
							{
								echo " <span class='exchangerix itooltip' title='".$erow['reason']."'><img src='".SITE_URL."images/info.png' align='absmiddle' /></span>";
							}
					?>
					<?php /*if ($erow['status'] == "pending") { ?><a class="btn btn-warning" href="#">make payment</a><?php } //dev */ ?>
				  </td>
                </tr>
			<?php } ?>
			
			<?php }else{ ?>
				<tr height="30"><td colspan="6" align="center" valign="middle"><br><p>You do not have exchanges at this time.</p></td></tr>
			<?php } ?>
		   </table>
			</div>
		
		
	</div>
	<div class="col-md-6 text-center">
	
					<h2 class="text-right"><i class="fa fa-user-circle fa-2x hidden-xs" style="vertical-align: middle; color: #d2e7a8"></i> <?php echo $row['fname']." ".$row['lname']; ?> Stats</h2>
					<div class="widget" style="width: 95%; margin-left: 20px; background: #F9F9F9">
					<div class="row">
						<div class="col-md-3 text-center">				
								<span class="total_balance_" style="font-size: 30px; color: #5bbc2e;"><b><?php echo GetUserBalance($row['user_id']); ?></b></span><br>
								<?php if (GetUserBalance($row['user_id'], 1) > 0) { ?><br><a href="<?php SITE_URL; ?>mybalance.php">view payments</a><br><?php } ?>
								<h4 style="margin-top: 10px">Account Balance</h4>
								
								<br><a class="btn btn-success" href="<?php SITE_URL; ?>withdraw.php"><i class="fa fa-money"></i> withdraw</a>
						</div>
						<div class="col-md-3 text-center">
							<a href="<?php SITE_URL; ?>mybalance.php#exchanges"><span style="font-size: 20px; color: #FFF; padding: 8px 15px; margin: 8px 0" class="badge">
								<?php echo GetUserExchangesTotal($row['user_id']); ?></span></a>		
							<h4><i class="fa fa-refresh fa-lg"></i> Exchanges</h4>
						</div>
						<div class="col-md-3 text-center">
							<a href="<?php SITE_URL; ?>myreviews.php"><span style="font-size: 20px; color: #FFF; padding: 8px 15px; margin: 8px 0" class="badge"><?php echo GetUserReviewsTotal($row['user_id']); ?></span></a>							
							<h4><i class="fa fa-comments-o"></i> Testimonials</h4>
						</div>
						<div class="col-md-3 text-center">
<a href="<?php SITE_URL; ?>invite.php#referrals"><span style="font-size: 20px; color: #FFF; padding: 8px 15px; margin: 8px 0" class="badge"><?php echo GetReferralsTotal($row['user_id']); ?></span></a>							
							<h4><i class="fa fa-users fa-lg"></i> Referrals</h4>	
						</div>						
					</div>
					</div>
					
	</div>	
	</div>
	

	<br><br>
	<hr>
	<div id="verification"></div>
	<div class="row">
	<div class="col-md-6">
				
				<?php if ((EMAIL_VERIFICATION == 1 || PHONE_VERIFICATION == 1 || DOCUMENT_VERIFICATION == 1 || ADDRESS_VERIFICATION == 1)) { ?>
					<h2><i class="fa fa-id-card-o" aria-hidden="true"></i> Account Verification</h2>

					<?php if (PHONE_VERIFICATION == 1 && CheckIfVerified($userid, "phone") && isset($_GET['msg']) && is_numeric($_GET['msg'])) { ?>
						<div class="alert alert-success">
							<i class="fa fa-check"></i>
							<?php
								switch($_GET['msg'])
								{
									case 2: echo "Thank you. Your phone number was verified!"; break;
									case 3: echo "Thank you. Document was uploaded and awaiting for administrator review."; break;
									case 4: echo "Thank you. Document was uploaded and awaiting for administrator review."; break;
								}
							?>
						</div>
					<?php } ?>					
					
					<div class="widget">
					<table width="95%" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<?php if (EMAIL_VERIFICATION == 1) { ?>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-envelope fa-lg"></i> Email<br> verified</h4>
							<h3>
								<?php if ($row['verified_email'] == 1) { ?>
									<i class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
						</td>
						<?php } ?>
						<?php if (PHONE_VERIFICATION == 1) { ?>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-phone-square fa-lg"></i> Phone<br> verified</h4>
							<h3>
								<?php if ($row['verified_phone'] == 1) { ?>
									<i class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }elseif ($row['verified_phone'] == 0 && $row['sms_code'] != "") { ?>
									<i id="itooltip" title="SMS sent, waiting for user's confirmation" class="fa fa-clock-o fa-lg" aria-hidden="true" style="color: #f39425"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
						</td>
						<?php } ?>
						<?php if (DOCUMENT_VERIFICATION == 1) { ?>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-address-card-o fa-lg"></i> Document<br> verified</h4>
							<h3>
								<?php if ($row['verified_document'] == 1) { ?>
									<i class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }elseif (strlen($row['verified_document']) > 10) { ?>
									<i title="Document uploaded and waiting for admin verification" class="fa fa-clock-o fa-lg itooltip" aria-hidden="true" style="color: #f39425"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
						</td>
						<?php } ?>
						<?php if (ADDRESS_VERIFICATION == 1) { ?>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-map-marker fa-lg"></i> Address<br> verified</h4>
							<h3>
								<?php if ($row['verified_address'] == 1) { ?>
									<i class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }elseif (strlen($row['verified_address']) > 10) { ?>
									<i title="Document uploaded and waiting for admin verification" class="fa fa-clock-o fa-lg itooltip" aria-hidden="true" style="color: #f39425"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
						</td>
						<?php } ?>						
					</tr>
					</table>
					
					 <br>					
					 <h4>Account Verification Status</h4>
					 <br>
					 <?php if ($row['verification_progress'] >= 99) { ?>
					 <div class="alert alert-success">
						<i class="fa fa-check-circle-o fa-lg"></i> Congratulations! Your account is fully activated and verified.
					 </div>
					 <?php }else{ ?>
						<div class="progress">
						  <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo (int)$row['verification_progress']; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo (int)$row['verification_progress']; ?>%">
						    <?php echo (int)$row['verification_progress']; ?>%
						  </div>
						</div>			 
					 <?php } ?>					
					
					</div>
			<?php } ?>	
	
	</div>
	<div class="col-md-6">
		
		<?php /*if ((EMAIL_VERIFICATION == 1 && !CheckIfVerified($userid, "email")) || (PHONE_VERIFICATION == 1 && !CheckIfVerified($userid, "phone")) || (DOCUMENT_VERIFICATION == 1 && !CheckIfVerified($userid, "document")) || (ADDRESS_VERIFICATION == 1 && !CheckIfVerified($userid, "address"))) { ?>
		<h2><i class="fa fa-question-circle"></i> How to verify your account:</h2>

		<div class="alert alert-info">
			<ul>
			<?php if (EMAIL_VERIFICATION == 1 && !CheckIfVerified($userid, "email")) { ?>
				<li>You need to <a href="#email">confirm your email address</a>.</li>
			<?php } ?>
			<?php if (PHONE_VERIFICATION == 1 && !CheckIfVerified($userid, "phone")) { ?>
				<li>You need to <a href="#mobile">confirm your mobile</a> number.</li>
			<?php } ?>
			<?php if (DOCUMENT_VERIFICATION == 1 && !CheckIfVerified($userid, "document")) { ?>
				<li>You need to <a href="#document">upload your documents</a>.</li>
			<?php } ?>
			<?php if (ADDRESS_VERIFICATION == 1 && !CheckIfVerified($userid, "address")) { ?>
				<li>You need to <a href="#address">upload photo</a> with your address (any bills, etc).</li>
			<?php } ?>			
			</ul>
		</div>
		<?php }*/ ?>
		
	
		<?php if (DOCUMENT_VERIFICATION == 1 && !CheckIfVerified($userid, "document") && $row['verified_document'] == "") { ?>
		<div class="widget" style="background: #F9F9F9">
		<a name="document"></a>
		<h3><i class="fa fa-address-card-o"></i> Upload Your Documents</h3>		
		<?php if (isset($allerrors) && $allerrors != "" && isset($_POST['action']) && $_POST['action'] == "upload_document") { ?>
			<div class='alert alert-danger'><?php echo $allerrors; ?></div>
		<?php } ?>					
		<p>You can upload your driver's license, state ID or passport.</p>
		<form action="" method="post" enctype="multipart/form-data" name="form1">
			<input type="file" name="upfile" class="form-control" accept="image/*" required />
			<input type="hidden" name="action" id="action" value="upload_document">
			<button type="submit" class="btn btn-success" name="add" id="add"><i class="fa fa-upload"></i> Upload</button>	
		</form>
		</div>
		<?php } ?>
		
		<?php if (PHONE_VERIFICATION == 1 && !CheckIfVerified($userid, "phone")) { ?>
		<div class="widget" style="background: #F9F9F9">
		<a name="mobile"></a>
		<h3><i class="fa fa-phone-square"></i> Verify Your Mobile Number</h3>
			<?php if (isset($_GET['msg']) && is_numeric($_GET['msg']) && !$_POST['action']) { ?>
				<div class="alert alert-success">
					<i class="fa fa-check"></i>
					<?php
	
						switch ($_GET['msg'])
						{
							case "1": echo "SMS verification code was successfully sent to your phone number!"; $blocked = 1; break;
							//case "2": echo "Thank you. Your phone number was verified!"; break;
						}
	
					?>
				</div>
			<?php } ?>
			<?php
					if (count($errs) > 0 && isset($_POST['action']) && $_POST['action'] == "verify_phone")
					{
						if (isset($allerrors) && $allerrors != "") echo "<div class='alert alert-danger'><i class='fa fa-remove'></i> ".$allerrors."</div>";
					}
			?>
				
		<?php if ($row['phone'] == 0) { ?>
			<div class="alert alert-info"><i class="fa fa-info-circle"></i> Please fill your phone number on <a href="<?php SITE_URL; ?>myprofile.php">Profile page</a>.</div>
		<?php }else{ ?>
			<form action="#verification" method="post" name="form2" class="form-inline">
			<?php if ($row['verified_phone'] == 0 && $row['sms_code'] == "") { ?>
				<p>Click button and we'll send SMS code on your phone number <b><?php echo $row['phone']; ?></b>.</p>
				<input type="hidden" name="action" id="action" value="send_sms">
				<button type="submit" class="btn btn-success" name="send" id="send">Send SMS</button>			
			<?php }else{ ?>
				<p>Please enter verification code from SMS and click Verify Phone.</p>
				<div class="form-group">
					<input type="hidden" name="action" id="action" value="verify_phone">
					<input type="textbox" name="sms_code" class="form-control" placeholder="xxxxxxxx" required />
				</div>
				<button type="submit" class="btn btn-success" name="verify" id="verify">Verify Phone</button>				
			<?php } ?>
		<?php } ?>
		</form>
		</div>
		<?php } ?>
		
		<?php if (ADDRESS_VERIFICATION == 1 && !CheckIfVerified($userid, "address") && $row['verified_address'] == "") { ?>
		<div class="widget" style="background: #F9F9F9">
		<a name="address"></a>
		<h3><i class="fa fa-map-marker"></i> Verify Your Address</h3>
			<?php
					if (count($errs) > 0  && isset($_POST['action']) && $_POST['action'] == "upload_address")
					{
						if (isset($allerrors) && $allerrors != "") echo "<div class='alert alert-danger'><i class='fa fa-remove'></i> ".$allerrors."</div>";
					}
			?>				
		<p>Please upload any document with your address.</p>
		<form action="" method="post" enctype="multipart/form-data" name="form1">
			<input type="file" name="upfile" class="form-control" accept="image/*" required />
			<input type="hidden" name="action" id="action" value="upload_address">
			<button type="submit" class="btn btn-success" name="add" id="add"><i class="fa fa-upload"></i> Upload</button>	
		</form>
		</div>
		<?php } ?>
		
		<div class="widget">
			<h3><i class="fa fa-info-circle"></i> Identity Upload Troubleshooting</h3>
			<p>If you are having difficulty successfully uploading a passport, driver's license or state ID, please ensure the following:</p>
			<ul>
				<li>You have a webcam and Adobe Flash is enabled in your browser</li>
				<li>The document is not expired or considerably damaged</li>
				<li>The image is clear and all borders of the document are fully visible</li>
				<li>The image is bright and well lit</li>
				<li>There is no glare on the document</li>
			</ul>			
		</div>	
	
	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>