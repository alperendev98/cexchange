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

	$cpage = 2;

	CheckAdminPermissions($cpage);

	if (isset($_POST['action']) && $_POST['action'] == "edit")
	{
		unset($errs);
		$errs = array();

		$user_id		= (int)getPostParameter('userid');
		$user_group		= (int)getPostParameter('user_group');
		$username		= mysqli_real_escape_string($conn, getPostParameter('username'));
		$fname			= mysqli_real_escape_string($conn, getPostParameter('fname'));
		$lname			= mysqli_real_escape_string($conn, getPostParameter('lname'));
		$email			= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$address		= mysqli_real_escape_string($conn, getPostParameter('address'));
		$address2		= mysqli_real_escape_string($conn, getPostParameter('address2'));
		$city			= mysqli_real_escape_string($conn, getPostParameter('city'));
		$state			= mysqli_real_escape_string($conn, getPostParameter('state'));
		$zip			= mysqli_real_escape_string($conn, getPostParameter('zip'));
		$country		= (int)getPostParameter('country');
		$phone			= mysqli_real_escape_string($conn, getPostParameter('phone'));
		$pwd			= mysqli_real_escape_string($conn, getPostParameter('password'));
		$pwd2			= mysqli_real_escape_string($conn, getPostParameter('password2'));
		$ref_id			= (int)getPostParameter('referer_id');
		$discount		= (int)getPostParameter('discount'); //dev

		$verified_email		= (int)getPostParameter('verified_email');
		$verified_document	= (int)getPostParameter('verified_document');
		$verified_phone		= (int)getPostParameter('verified_phone');
		$verified_address	= (int)getPostParameter('verified_address');
		
		$newsletter		= (int)getPostParameter('newsletter');
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));

		$flag = 0;

		if (!($username && $fname && $lname && $email && $status))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if (isset($discount) && $discount != "" && !is_numeric($discount))
				$errs[] = "Please enter correct discount value";			
		}
		

		if(isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = "Invalid email address";
		}

		if (isset($pwd) && $pwd != "" && isset($pwd2) && $pwd2 != "")
		{
			if ($pwd !== $pwd2)
			{
				$errs[] = "Password confirmation is wrong";
			}
			elseif ((strlen($pwd)) < 6 || (strlen($pwd2) < 6) || (strlen($pwd)) > 20 || (strlen($pwd2) > 20))
			{
				$errs[] = "Password must be between 6-20 characters (letters and numbers)";
			}
			elseif (stristr($pwd, ' '))
			{
				$errs[] = "Password must not contain spaces";
			}
			else
			{
				$flag = 1;
			}
		}

		if (count($errs) == 0)
		{
			$e = 0;
			$verification_progress = 0;
			
			if (EMAIL_VERIFICATION == 1) $e+=1;
			if (PHONE_VERIFICATION == 1) $e+=1;
			if (DOCUMENT_VERIFICATION == 1) $e+=1;
			if (ADDRESS_VERIFICATION == 1) $e+=1;
			
			if ($e > 0)
			{
				$progress_percent = floor(100/$e);
				
				if ($verified_email	== 1) $verification_progress+=$progress_percent;
				if ($verified_document == 1) $verification_progress+=$progress_percent;
				if ($verified_phone	== 1) $verification_progress+=$progress_percent;
				if ($verified_address == 1) $verification_progress+=$progress_percent;	
			}		
			
			if ($flag == 1) $asql = "password='".PasswordEncryption($pwd)."',"; else $asql = "";

			$sql = "UPDATE exchangerix_users SET user_group='$user_group', username='$username', ".$asql." email='$email', fname='$fname', lname='$lname', address='$address', address2='$address2', city='$city', state='$state', zip='$zip', country='$country', phone='$phone', ref_id='$ref_id', newsletter='$newsletter', discount='$discount', verified_email='$verified_email', verified_document='$verified_document', verified_phone='$verified_phone', verified_address='$verified_address', verification_progress='$verification_progress', status='$status' WHERE user_id='$user_id' LIMIT 1";

			smart_mysql_query($sql);

			header("Location: users.php?msg=updated");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid = (int)$_GET['id'];

		$query = "SELECT * FROM exchangerix_users WHERE user_id='$uid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Edit User";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {  $row = mysqli_fetch_array($result); ?>

        <h2><i class="fa fa-user" aria-hidden="true"></i> Edit User</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<!--<img src="images/user.png" class="imgs" style="position: absolute; right: 10px; margin-top: 5px;" />-->

        <form action="" method="post">
          <table width="100%" style="background:#F9F9F9" style="padding: 10px 0;" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1">User ID:</td>
            <td valign="top"><input type="text" name="" value="<?php echo $row['user_id']; ?>" size="12" class="form-control" disabled /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-users"></i> User Group:</td>
            <td valign="top">
				<select name="user_group" class="selectpicker show-menu-arrow show-tick form-control" id="user_group" data-width="fit">
					<option value="0" <?php if ($row['user_group'] == 0) echo "selected"; ?> data-content="<i class='fa fa-user-o'></i> Regular User">>Regular User</option>
					<option value="2" <?php if ($row['user_group'] == 2) echo "selected"; ?> data-content="<i class='fa fa-headphones'></i> Operator">>Operator</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Username:<span class="req">* </span></td>
            <td valign="top"><input type="text" name="username" id="username" value="<?php echo $row['username']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">First Name:<span class="req">* </span></td>
            <td valign="top"><input type="text" name="fname" id="fname" value="<?php echo $row['fname']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Name:<span class="req">* </span></td>
            <td valign="top"><input type="text" name="lname" id="lname" value="<?php echo $row['lname']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Email:<span class="req">* </span></td>
            <td valign="top"><input type="text" name="email" id="email" value="<?php echo $row['email']; ?>" size="32" class="form-control" /> <!-- //dev veridief yes NO --></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Address Line 1:</td>
            <td valign="top"><input type="text" class="form-control" name="address" id="address" value="<?php echo $row['address']; ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Address Line 2:</td>
            <td valign="top"><input type="text" class="form-control" name="address2" id="address2" value="<?php echo $row['address2']; ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">City:</td>
            <td valign="top"><input type="text" class="form-control" name="city" id="city" value="<?php echo $row['city']; ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">State/Province:</td>
            <td valign="top"><input type="text" class="form-control" name="state" id="state" value="<?php echo $row['state']; ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Zip Code:</td>
            <td valign="top"><input type="text" class="form-control" name="zip" id="zip" value="<?php echo $row['zip']; ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Country:</td>
            <td valign="top">
				<select name="country" class="selectpicker show-menu-arrow show-tick" id="country">
				<option value="">-- Select country --</option>
				<?php

					$sql_country = "SELECT * FROM exchangerix_countries WHERE signup='1' AND status='active' ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							if ($row['country'] == $row_country['country_id'])
								echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']."</option>\n";
							else
								echo "<option value='".$row_country['country_id']."'>".$row_country['name']."</option>\n";
						}
					}

				?>
				</select>			
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Phone:</td>
            <td valign="middle"><input type="text" name="phone" id="phone" value="<?php echo $row['phone']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-user-circle"></i> Referrer ID:</td>
            <td valign="middle"><input type="text" name="referer_id" id="referer_id" value="<?php echo ($row['ref_id'] > 0) ? $row['ref_id'] : ""; ?>" size="6" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Discount:</td>
            <td valign="middle"><input type="text" name="discount" id="discount" value="<?php echo ($row['discount'] > 0) ? $row['discount'] : "0"; ?>" size="6" class="form-control" /> %</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_email" class="checkbox" value="1" <?php echo (@$row['verified_email'] == 1) ? "checked" : "" ?>/> Email verified</label></div></td>
          </tr>     
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_phone" class="checkbox" value="1" <?php echo (@$row['verified_phone'] == 1) ? "checked" : "" ?>/> Phone number verified</label></div></td>
          </tr>  
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_document" class="checkbox" value="1" <?php echo (@$row['verified_document'] == 1) ? "checked" : "" ?>/> ID/Passport verified</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_address" class="checkbox" value="1" <?php echo (@$row['verified_address'] == 1) ? "checked" : "" ?>/> Address verified</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="newsletter" class="checkbox" value="1" <?php echo (@$row['newsletter'] == 1) ? "checked" : "" ?>/> Subscribed to newsletter</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">New Password:</td>
            <td valign="top"><input type="password" name="password" id="password" value="" size="32" class="form-control" /><span class="note" title="Leave blank if you don't want to change the password"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Confirm Password:</td>
            <td valign="top"><input type="password" name="password2" id="password2" value="" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" class="selectpicker">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr>
			<td align="left" valign="bottom">&nbsp;</td>
			<td align="left" valign="bottom">
				<input type="hidden" name="userid" id="userid" value="<?php echo (int)$row['user_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" />
            </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no user found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>