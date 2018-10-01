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

	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		unset($errs);
		$errs = array();

		$user_group		= (int)getPostParameter('user_group');
		$fname			= mysqli_real_escape_string($conn, getPostParameter('fname'));
		$lname			= mysqli_real_escape_string($conn, getPostParameter('lname'));
		$email			= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$username		= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$random_pwd		= (int)getPostParameter('random_pwd');
		if ($random_pwd == 1) $pwd = mysqli_real_escape_string($conn, generatePassword(10)); else $pwd = mysqli_real_escape_string($conn, getPostParameter('password'));
		$address		= mysqli_real_escape_string($conn, getPostParameter('address'));
		$address2		= mysqli_real_escape_string($conn, getPostParameter('address2'));
		$city			= mysqli_real_escape_string($conn, getPostParameter('city'));
		$state			= mysqli_real_escape_string($conn, getPostParameter('state'));
		$zip			= mysqli_real_escape_string($conn, getPostParameter('zip'));
		$country		= (int)getPostParameter('country');
		$phone			= mysqli_real_escape_string($conn, getPostParameter('phone'));
		$send_details	= (int)getPostParameter('send_details');
		$signup_bonus	= (int)getPostParameter('signup_bonus');
		$ref_id			= (int)getPostParameter('referer_id');
		$discount		= (int)getPostParameter('discount');
		
		$verified_email		= (int)getPostParameter('verified_email');
		$verified_document	= (int)getPostParameter('verified_document');
		$verified_phone		= (int)getPostParameter('verified_phone');
		$verified_address	= (int)getPostParameter('verified_address');
		
		$newsletter		= (int)getPostParameter('newsletter');		
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));

		if(!($fname && $lname && $email && $pwd && $status))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if(isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				$errs[] = "Invalid email address";
			}

			if ((strlen($pwd) < 6) || (strlen($pwd) > 20))
			{
				$errs[] = "Password must be between 6-20 characters (letters and numbers)";
			}
			elseif (stristr($pwd, ' '))
			{
				$errs[] = "Password must not contain spaces";
			}

			if (isset($discount) && $discount != "" && !is_numeric($discount))
				$errs[] = "Please enter correct discount value";
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
			
			$unsubscribe_key = GenerateKey($username);

			$insert_query = "INSERT INTO exchangerix_users SET user_group='$user_group', username='$username', password='".PasswordEncryption($pwd)."', email='$email', fname='$fname', lname='$lname', address='$address', address2='$address2', city='$city', state='$state', zip='$zip', country='$country', phone='$phone', ref_id='$ref_id', newsletter='$newsletter', discount='$discount', verified_email='$verified_email', verified_document='$verified_document', verified_phone='$verified_phone', verified_address='$verified_address', verification_progress='$verification_progress', ip='111.111.111.111', status='$status', unsubscribe_key='$unsubscribe_key', created=NOW()";
			smart_mysql_query($insert_query);
			$new_user_id = mysqli_insert_id($conn);

			// save SIGN UP BONUS transaction //
			if ($signup_bonus == 1 && SIGNUP_BONUS > 0)
			{
				$reference_id = GenerateReferenceID();
				smart_mysql_query("INSERT INTO exchangerix_transactions SET reference_id='$reference_id', user_id='$new_user_id', payment_type='signup_bonus', amount='".SIGNUP_BONUS."', status='confirmed', created=NOW(), process_date=NOW()");
			}

			// send login info //
			if ($send_details == 1)
			{
				$etemplate = GetEmailTemplate('signup');
				$esubject = $etemplate['email_subject'];
				$emessage = $etemplate['email_message'];

				$emessage = str_replace("{first_name}", $fname, $emessage);
				$emessage = str_replace("{username}", $username, $emessage);
				$emessage = str_replace("{password}", $pwd, $emessage);
				if ($user_group > 0)
					$emessage = str_replace("{login_url}", SITE_URL."admin/", $emessage);
				else
					$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
				$to_email = $fname.' '.$lname.' <'.$email.'>';

				SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
			}

			header("Location: users.php");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}

	$title = "Add User";
	require_once ("inc/header.inc.php");

?>

        <h2><i class="fa fa-user-plus"></i> Add User</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<img src="images/user.png" class="imgs" style="position: absolute; right: 15px; margin-top: 5px;" />

        <form action="" method="post">
          <table width="100%" style="background:#F9F9F9" style="padding: 10px 0;" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1" style="padding-top: 8px"><i class="fa fa-users"></i> User Group:</td>
            <td valign="middle" style="padding-top: 8px">
				<select name="user_group" class="selectpicker show-menu-arrow show-tick form-control" id="user_group" data-width="fit">
					<option value="0" <?php if ($user_group == 0) echo "selected"; ?> data-content="<i class='fa fa-user-o'></i> Regular User">>Regular User</option>
					<option value="2" <?php if ($user_group == 2) echo "selected"; ?> data-content="<i class='fa fa-headphones'></i> Operator">>Operator</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">First Name:<span class="req">* </span></td>
            <td valign="middle"><input type="text" name="fname" id="fname" value="<?php echo getPostParameter('fname'); ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Name:<span class="req">* </span></td>
            <td valign="middle"><input type="text" name="lname" id="lname" value="<?php echo getPostParameter('lname'); ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Email:<span class="req">* </span></td>
            <td valign="middle"><input type="text" name="email" id="email" value="<?php echo getPostParameter('email'); ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="top" align="left" class="tb1" style="padding-top:6px;">Password:<span class="req">* </span></td>
            <td valign="top">
				<input type="password" name="password" id="password" value="" size="32" class="form-control" />
				<br/><div class="checkbox"><label><input type="checkbox" name="random_pwd" onclick="document.getElementById('password').disabled=this.checked;" value="1" <?php echo (@$random_pwd == 1) ? "checked" : "" ?>/> generate random password</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Address Line 1:</td>
            <td valign="top"><input type="text" class="form-control" name="address" id="address" value="<?php echo getPostParameter('address'); ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Address Line 2:</td>
            <td valign="top"><input type="text" class="form-control" name="address2" id="address2" value="<?php echo getPostParameter('address2'); ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">City:</td>
            <td valign="top"><input type="text" class="form-control" name="city" id="city" value="<?php echo getPostParameter('city'); ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">State/Province:</td>
            <td valign="top"><input type="text" class="form-control" name="state" id="state" value="<?php echo getPostParameter('state'); ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Zip Code:</td>
            <td valign="top"><input type="text" class="form-control" name="zip" id="zip" value="<?php echo getPostParameter('zip'); ?>" size="32" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Country:</td>
            <td valign="top">
				<select name="country" class="selectpicker" id="country" style="width: 195px;">
				<option value="">-- Select country --</option>
				<?php

					$sql_country = "SELECT * FROM exchangerix_countries WHERE signup='1' AND status='active' ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							if ($country == $row_country['country_id'])
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
            <td valign="top"><input type="text" name="phone" id="phone" value="<?php echo getPostParameter('phone'); ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-user-circle"></i> Referrer ID:</td>
            <td valign="top"><input type="text" name="referer_id" id="referer_id" value="<?php echo getPostParameter('referer_id'); ?>" size="6" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Discount:</td>
            <td valign="middle"><input type="text" name="discount" id="discount" value="<?php echo getPostParameter('discount'); ?>" size="6" class="form-control" /> %</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_email" class="checkbox" value="1" <?php echo (@$verified_email == 1) ? "checked" : "" ?>/> Email verified</label></div></td>
          </tr>     
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_phone" class="checkbox" value="1" <?php echo (@$verified_phone == 1) ? "checked" : "" ?>/> Phone number verified</label></div></td>
          </tr>  
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_document" class="checkbox" value="1" <?php echo (@$verified_document == 1) ? "checked" : "" ?>/> ID/Passport verified</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="verified_address" class="checkbox" value="1" <?php echo (@$verified_address == 1) ? "checked" : "" ?>/> Address verified</label></div></td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="send_details" class="checkbox" value="1" <?php echo (@$send_details == 1) ? "checked" : "" ?>/> Send email with login info to member</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="signup_bonus" class="checkbox" value="1" <?php echo (@$signup_bonus == 1) ? "checked" : "" ?>/> Add signup bonus</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle"><div class="checkbox"><label><input type="checkbox" name="newsletter" class="checkbox" value="1" <?php echo (@$newsletter == 1) ? "checked" : "" ?>/> Subscribe to newsletter</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" class="selectpicker">
					<option value="active" <?php if ($status == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($status == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr>
			<td align="left" valign="bottom">&nbsp;</td>
			<td align="left" valign="bottom">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add User" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" />
            </td>
          </tr>
        </table>
      </form>

<?php require_once ("inc/footer.inc.php"); ?>