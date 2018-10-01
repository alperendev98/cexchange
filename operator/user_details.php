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

	$cpage = 2;
	

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid	= (int)$_GET['id'];

		if (isset($_GET['action']) && $_GET['action'] == "block") BlockUnblockUser($uid);
		if (isset($_GET['action']) && $_GET['action'] == "unblock") BlockUnblockUser($uid,1);				

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS created, DATE_FORMAT(last_login, '".DATE_FORMAT." %h:%i %p') AS last_login FROM exchangerix_users WHERE user_id='$uid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
		
		if ($total > 0) $row = mysqli_fetch_array($result);


			// verification progress
			$e = 0;
			$verification_progress = 0;			
			if (EMAIL_VERIFICATION == 1) $e+=1;
			if (PHONE_VERIFICATION == 1) $e+=1;
			if (DOCUMENT_VERIFICATION == 1) $e+=1;
			if (ADDRESS_VERIFICATION == 1) $e+=1;
			if ($e > 0) $progress_percent = floor(100/$e);
		

		if (isset($_GET['action']) && $_GET['action'] == "confirm_document")
		{
			$verification_progress+=$progress_percent;
			
			smart_mysql_query("UPDATE exchangerix_users SET verified_document='1', verification_progress='$verification_progress' WHERE user_id='$uid' LIMIT 1");
			if (file_exists(PUBLIC_HTML_PATH."/uploads/".$row['verified_document'])) @unlink(PUBLIC_HTML_PATH."/uploads/".$row['verified_document']);			
			
			header("Location: user_details.php?id=".$uid."&msg=confirmed");
			exit();				
		}
		
		if (isset($_GET['action']) && $_GET['action'] == "decline_document")
		{
			smart_mysql_query("UPDATE exchangerix_users SET verified_document='0' WHERE user_id='$uid' LIMIT 1");
			if (file_exists(PUBLIC_HTML_PATH."/uploads/".$row['verified_document'])) @unlink(PUBLIC_HTML_PATH."/uploads/".$row['verified_document']);	
			header("Location: user_details.php?id=".$uid."&msg=declined");
			exit();				
		}
		
		if (isset($_GET['action']) && $_GET['action'] == "confirm_address")
		{
			$verification_progress+=$progress_percent;
			
			smart_mysql_query("UPDATE exchangerix_users SET verified_address='1', verification_progress='$verification_progress' WHERE user_id='$uid' LIMIT 1");
			if (file_exists(PUBLIC_HTML_PATH."/uploads/".$row['verified_address'])) @unlink(PUBLIC_HTML_PATH."/uploads/".$row['verified_address']);	
			header("Location: user_details.php?id=".$uid."&msg=confirmed");
			exit();				
		}
		
		if (isset($_GET['action']) && $_GET['action'] == "decline_address")
		{
			smart_mysql_query("UPDATE exchangerix_users SET verified_address='0' WHERE user_id='$uid' LIMIT 1");
			if (file_exists(PUBLIC_HTML_PATH."/uploads/".$row['verified_address'])) @unlink(PUBLIC_HTML_PATH."/uploads/".$row['verified_address']);	
			header("Location: user_details.php?id=".$uid."&msg=declined");
			exit();			
		}
		
	}

	$title = "User Details";
	require_once ("inc/header.inc.php");

?>
	
      <h2><i class="fa fa-user" aria-hidden="true"></i> User Details</h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
					switch ($_GET['msg'])
					{
						case "confirmed": echo "Document was approved"; break;
					}
			?>
		</div>
		<?php } ?>

      <?php if ($total > 0) { ?>

          <table style="background:#F9F9F9" width="100%" align="center" cellpadding="2" cellspacing="5" border="0">
          <tr>
			<td width="40%" valign="top" style="background: #F5F5F5">
			
			<br>
		  <table width="90%" align="center" cellpadding="3" cellspacing="5" border="0">
		  <tr>
           <td width="35%" valign="middle" align="left" class="tb1">User ID:</td>
           <td align="left" valign="middle"><?php echo $row['user_id']; ?></td>
          </tr>
		  <tr>
           <td valign="middle" align="left" class="tb1">Role:</td>
           <td align="left" valign="middle">
	           <span class="badge" style="background: #a3b7ba">
				<?php
					switch ($row['user_group'])
					{
						case "0": echo "Regular User"; break;
						case "2": echo "<i class='fa fa-user-circle-o'></i> Operator"; break;
						default: echo $row['user_group']; break;
					}
				?>
	           </span>
		   </td>
          </tr>
          <?php if ($row['user_group'] == 2) { ?>
           <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-headphones"></i> Operator login:</td>
            <td align="left" valign="middle"><a href="<?php echo SITE_URL."operator/"; ?>" target="_blank"><small><?php echo SITE_URL."operator/"; ?></small></a></td>
          </tr>          
          <?php } ?>
           <tr>
            <td valign="middle" align="left" class="tb1">Username:</td>
            <td align="left" valign="middle"><b><?php echo $row['username']; ?></b></td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">First Name:</td>
            <td align="left" valign="middle"><?php echo $row['fname']; ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Name:</td>
            <td align="left" valign="middle"><?php echo $row['lname']; ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Email:</td>
            <td align="left" valign="middle">
				<a href="email2users.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['email']; ?></a>
				<?php /*if ($row['activation_key'] != "") { ?> <sup style="background: #c9c8c7; color: #FFF; padding: 1px 3px; border-radius: 3px;">not verified</sup><?php }*/ ?>
				<?php if($row['verified_email'] == 1) { ?> <sup id="itooltip" style="background: #5bbc2e; color: #FFF; padding: 2px 5px; margin-left: 5px; border-radius: 3px;" title="email verified">verified</sup><?php }else{ ?> <sup style="background: #999; color: #FFF; padding: 2px 5px; border-radius: 3px; margin-left: 5px;">not verified</sup><?php } ?>
			</td>
          </tr>
		  <?php if ($row['address'] != "") { ?>
		  <tr>
            <td valign="middle" align="left" class="tb1">Address Line 1:</td>
            <td align="left" valign="middle"><?php echo $row['address']; ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['address2'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Address Line 2:</td>
            <td align="left" valign="middle"><?php echo $row['address2']; ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['city'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">City:</td>
            <td align="left" valign="middle"><?php echo $row['city']; ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['state'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">State/Province:</td>
            <td align="left" valign="middle"><?php echo $row['state']; ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['zip'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Zip Code:</td>
            <td align="left" valign="middle"><?php echo $row['zip']; ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['country'] != "0") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Country:</td>
            <td align="left" valign="middle"><?php echo GetCountry($row['country']); ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['phone'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Phone:</td>
            <td align="left" valign="middle">
	            <?php echo $row['phone']; ?>
				<?php if($row['verified_phone'] == 1) { ?> <sup id="itooltip" style="background: #5bbc2e; color: #FFF; padding: 2px 5px; margin-left: 5px; border-radius: 3px;" title="phone number verified">verified</sup><?php }else{ ?> <sup style="background: #999; color: #FFF; padding: 2px 5px; border-radius: 3px; margin-left: 5px;">not verified</sup><?php } ?>
            </td>
          </tr>
		  <?php } ?>
		  <?php if ($row['discount'] > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Discount:</td>
            <td align="left" valign="middle"><?php echo $row['discount']; ?>%</td>
          </tr>
		  <?php } ?>		  
		  <?php if ($row['reg_source'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">How did you hear about us:</td>
            <td align="left" valign="middle"><?php echo $row['reg_source']; ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Newsletter:</td>
            <td align="left" valign="middle">
				<?php if ($row['newsletter'] == 1) { ?>
					<i id="itooltip" title="Subscribed to newsletter" class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
				<?php }else{ ?>
					<i id="itooltip" title="Not subscribed to newsletter" class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
				<?php } ?>
            </td>
          </tr>

          
		  <?php if ($row['auth_provider'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Signup via:</td>
            <td align="left" valign="middle"><?php echo $row['auth_provider']; ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Signup Date:</td>
            <td align="left" valign="middle"><?php echo $row['created']; ?></td>
          </tr>
		  <?php if ($row['ip'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">IP Address:</td>
            <td align="left" valign="middle"><?php echo $row['ip']; ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Login Count:</td>
            <td align="left" valign="middle"><?php echo $row['login_count']; ?></td>
          </tr>
		  <?php if ($row['login_count'] > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Login:</td>
            <td align="left" valign="middle"><?php echo $row['last_login']; ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last logged IP:</td>
            <td align="left" valign="middle"><?php echo $row['last_ip']; ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td align="left" valign="middle">
				<?php if ($row['status'] == "inactive") echo "<span class='inactive_s'>".$row['status']."</span>"; else echo "<span class='active_s'>".$row['status']."</span>"; ?>
				<?php if ($row['status'] == "inactive" && $row['activation_key'] != "") { ?> <sup style="background: #c9c8c7; color: #fff; padding: 1px 4px; border-radius: 3px;">(awaiting activation by email)</sup><?php } ?>
			</td>
          </tr>
		  <?php if ($row['status'] == "inactive" && $row['block_reason'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Block Reason:</td>
            <td align="left" valign="middle"><?php echo $row['block_reason']; ?></td>
          </tr>
		  <?php } ?>
		  </table>

			</td>
			<td width="2%" valign="top">&nbsp;</td>
			<td width="50%" valign="top">

			  <table width="100%" align="center" cellpadding="3" cellspacing="5" border="0">
			  <tr>
				<td align="left" valign="middle">
					
					&nbsp;&nbsp;<h4 style="margin: 0" class="text-center"><p data-letters="<?php echo $row['fname'][0].$row['lname'][0]; ?>"><b><?php echo $row['fname']." ".$row['lname']; ?></b></p></h4>
					<table width="95%" bgcolor="#F7F7F7" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-money"></i><br> Account Balance</h4>
							<div class="abalance">
								<span class="total_balance"><?php echo GetUserBalance($row['user_id']); ?></span>
								<?php if (GetUserBalance($row['user_id'], 1) > 0) { ?><br/><a href="user_payments.php?id=<?php echo $row['user_id']; ?>">view payments</a><?php } ?>
							</div>
						</td>
						<!--
							<td width="30%" align="center" valign="top">
							<h3>Total Payments</h3>
							<br/>
							<h3>
							<?php
								/*$cash_row = mysqli_fetch_array(smart_mysql_query("SELECT SUM(amount) AS total FROM exchangerix_transactions WHERE user_id='".(int)$uid."' AND status='confirmed' OR status='pending'"));
								echo DisplayMoney($cash_row['total']);*/
							?>
							</h3>
						</td>
						-->
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-refresh"></i><br> Exchanges</h4>
							<!--<a href="user_payments.php?id=<?php echo $row['user_id']; ?>">-->
							<a href="exchanges.php?filter=<?php echo $row['user_id']; ?>&search_type=member&action=filter"><span style="font-size: 18px; color: #FFF; padding: 5px 11px;" class="badge"><?php echo GetUserExchangesTotal($row['user_id']); ?></span></a>
						</td>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-comments-o"></i><br> Testimonials</h4>
							<a href="reviews.php?user=<?php echo $row['user_id']; ?>"><span style="font-size: 18px; color: #FFF; padding: 5px 11px;" class="badge"><?php echo GetUserReviewsTotal($row['user_id']); ?></span></a>
						</td>
						<td width="25%" align="center" valign="top">
							<h4><i class="fa fa-users"></i><br> Referrals</h4>
							<a href="user_referrals.php?id=<?php echo $row['user_id']; ?>"><span style="font-size: 18px; color: #FFF; padding: 5px 11px;" class="badge"><?php echo GetReferralsTotal($row['user_id']); ?></span></a>
						</td>						
					</tr>
					</table>
					<hr>
					
					<h3 class="text-center">Account Verification</h3>
					<br>
					<table width="95%" bgcolor="#F7F7F7" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<td width="20%" align="center" valign="top">
							<h4><i class="fa fa-envelope"></i> Email<br> verified</h4>
							<h3>
								<?php if ($row['verified_email'] == 1) { ?>
									<i class="fa fa-check-circle-o" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
						</td>
						<td width="20%" align="center" valign="top">
							<h4><i class="fa fa-phone-square"></i> Phone<br> verified</h4>
							
								<?php if ($row['verified_phone'] == 1) { ?>
									<h3><i class="fa fa-check-circle-o" aria-hidden="true" style="color: #1fb40e"></i></h3>
								<?php }elseif ($row['verified_phone'] == 0 && $row['sms_code'] != "") { ?>
									<h3><i class="fa fa-clock-o" aria-hidden="true" style="color: #f39425"></i></h3>
									SMS sent and waiting for user's confirmation
								<?php }else{ ?>
									<h3><i class="fa fa-times-circle-o" aria-hidden="true" style="color: #797474"></i></h3>
								<?php } ?>
							
						</td>
						<td width="30%" align="center" valign="top">
							<h4><i class="fa fa-address-card-o"></i> Document<br> verified</h4>
							
								<?php if ($row['verified_document'] == 1) { ?>
									<h3><i class="fa fa-check-circle-o" aria-hidden="true" style="color: #1fb40e"></i></h3>
								<?php }elseif (strlen($row['verified_document']) > 10) { ?>
									<h3><i class="fa fa-clock-o tooltips" aria-hidden="true" title="document uploaded and waiting for admin verification" style="color: #f39425"></i></h3>
									<span class="label label-warning">awating review</span>
									<br>
									<i class="fa fa-paperclip"></i> <a href="<?php echo SITE_URL; ?>uploads/<?php echo $row['verified_document']; ?>"  data-lightbox="image-1" data-title="Document">view file</a><br>
									<br>
									<a style="color: #eb4343" class="tooltips" title="decline and delete file" href="user_details.php?id=<?php echo $row['user_id']; ?>&action=decline_document"><i class="fa fa-times" stye="color: #d34835"></i> decline</a>									
									&nbsp;<a style="color:#50aa23" class="tooltips" title="confirm document" href="user_details.php?id=<?php echo $row['user_id']; ?>&action=confirm_document"><i class="fa fa-check"></i> confirm</a>
									<br><br>
								<?php }else{ ?>
									<h3><i class="fa fa-times-circle-o" aria-hidden="true" style="color: #797474"></i>
							</h3>
								<?php } ?>
						</td>
						<td width="30%" align="center" valign="top">
							<h4><i class="fa fa-map-marker"></i> Address<br> verified</h4>
							
								<?php if ($row['verified_address'] == 1) { ?>
									<h3><i class="fa fa-check-circle-o" aria-hidden="true" style="color: #1fb40e"></i></h3>
								<?php }elseif (strlen($row['verified_address']) > 10) { ?>
									<h3><i class="fa fa-clock-o tooltips" aria-hidden="true" title="document uploaded and waiting for admin verification" style="color: #f39425"></i></h3>
									<span class="label label-warning">awating review</span>
									<br>
									<i class="fa fa-paperclip"></i> <a href="<?php echo SITE_URL; ?>uploads/<?php echo $row['verified_address']; ?>" data-lightbox="image-1" data-title="Address Document Proof">view file</a><br>
									<br>
									<a style="color: #eb4343" class="tooltips" title="decline and delete file" href="user_details.php?id=<?php echo $row['user_id']; ?>&action=decline_address"><i class="fa fa-times" stye="color: #d34835"></i> decline</a>									
									&nbsp;<a style="color:#50aa23" class="tooltips" title="confirm address" href="user_details.php?id=<?php echo $row['user_id']; ?>&action=confirm_address"><i class="fa fa-check"></i> confirm</a>
									<br><br>
								<?php }else{ ?>
									<h3><i class="fa fa-times-circle-o" aria-hidden="true" style="color: #797474"></i></h3>
								<?php } ?>
							
						</td>						
					</tr>
					<?php if (EMAIL_VERIFICATION == 1 || PHONE_VERIFICATION == 1 || DOCUMENT_VERIFICATION == 1 || ADDRESS_VERIFICATION == 1) { ?>
					<tr>
						<td colspan="4" align="center">
							<p class="text-center">
								<div class="progress">
								  <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $row['verification_progress']; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $row['verification_progress']; ?>%">
								    <?php echo $row['verification_progress']; ?>%
								  </div>
								</div>		
							</p>
						</td>
					</tr>
					<?php } ?>					
					</table>
					
				</td>
			  </tr>
			  </table>
			  <br><br>
			
			</td>
		  </tr>
		  <tr>
			<td colspan="3" valign="top">

			  <table width="100%" align="center" cellpadding="3" cellspacing="5" border="0">
			  <tr>
				<td bgcolor="#F9F9F9" height="50" style="border-top: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee;" colspan="2" align="center" valign="middle">
					<a class="emailit" href="email2users.php?id=<?php echo $row['user_id']; ?>">Send Email</a>
					<?php if ($row['status'] == "active") { ?>
						<a class="blockit" href="user_details.php?id=<?php echo $row['user_id']; ?>&action=block">Block User</a>
					<?php }else{ ?>
						<a class="unblockit" href="user_details.php?id=<?php echo $row['user_id']; ?>&action=unblock">UnBlock User</a>
					<?php } ?>
				</td>
			  </tr>
			  </table>

			</td>
		  </tr>
		  </table>
			  
			  <p class="text-center">
					<a class="btn btn-success" href="user_edit.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-pencil-square-o"></i> Edit User</a>
					<a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back <i class="fa fa-angle-right" aria-hidden="true"></i></a>
					<a class="btn btn-danger pull-right" href="#" onclick="if (confirm('Are you sure you really want to delete this user?') )location.href='users.php?id=<?php echo $row['user_id']; ?>&action=delete';"><i class="fa fa-user-times" aria-hidden="true"></i> Delete User</a>
			  </p>

	  <?php }else{ ?>
			<div class="alert alert-info">Sorry, no user found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>