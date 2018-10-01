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

	$cpage = 23;

	CheckAdminPermissions($cpage);

	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		$email_name		= mysqli_real_escape_string($conn, $_POST['email_name']);
		$language		= mysqli_real_escape_string($conn, $_POST['language']);
		$email_subject	= mysqli_real_escape_string($conn, $_POST['esubject']);
		$email_message	= mysqli_real_escape_string($conn, $_POST['emessage']);

		if ($_POST['add'] && $_POST['add'] != "")
		{
			unset($errs);
			$errs = array();

			if (!($email_name && $language && $email_subject && $email_message))
			{
				$errs[] = "Please fill in all required fields";
			}
			else
			{
				$check_query = smart_mysql_query("SELECT * FROM exchangerix_email_templates WHERE language='$language' AND email_name='$email_name' AND email_name!='email2users'");
				if (mysqli_num_rows($check_query) != 0)
				{
					$errs[] = "Sorry, that email template already exists";
				}
			}

			if (count($errs) == 0)
			{
				$sql = "INSERT INTO exchangerix_email_templates SET language='$language', email_name='$email_name', email_subject='$email_subject', email_message='$email_message', modified=NOW()";

				if (smart_mysql_query($sql))
				{
					header("Location: etemplates.php?msg=added");
					exit();
				}
			}
			else
			{
				$allerrors = "";
				foreach ($errs as $errorname)
					$allerrors .= $errorname."<br/>";
			}
		}
	}

	$title = "Add Email Template";
	require_once ("inc/header.inc.php");

?>

        <h2><i class="fa fa-envelope" aria-hidden="true"></i> Add Email Template</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" name="form1" method="post">
          <table style="background:#F9F9F9" width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td  width="35" valign="middle" align="right" class="tb1">Email Template:</td>
            <td valign="top">
				<select name="email_name" onChange="document.form1.submit()" class="selectpicker">
					<option value="">-- select email template --</option>
					<option value="signup" <?php if ($email_name == "signup") echo "selected='selected'"; ?>>Sign Up email</option>
					<option value="activate" <?php if ($email_name == "activate") echo "selected='selected'"; ?>>Registration Confirmation email</option>
					<option value="activate2" <?php if ($email_name == "activate2") echo "selected='selected'"; ?>>Account activation email</option>
					<option value="forgot_password" <?php if ($email_name == "forgot_password") echo "selected='selected'"; ?>>Forgot Password email</option>
					<option value="invite_friend" <?php if ($email_name == "invite_friend") echo "selected='selected'"; ?>>Invite a Friend email</option>
					<!--
					<option value="cashout_paid" <?php if ($email_name == "cashout_paid") echo "selected='selected'"; ?>>Payment Declined</option>
					<option value="cashout_declined" <?php if ($email_name == "cashout_declined") echo "selected='selected'"; ?>>Cash Out declined email</option>
					<option value="manual_credit" <?php if ($email_name == "manual_credit") echo "selected='selected'"; ?>>Manual Payment email</option>-->
					<option value="email2users" <?php if ($email_name == "email2users") echo "selected='selected'"; ?>>Email Members email</option>
				</select>			
			</td>
          </tr>
          <tr>
            <td  width="35" valign="middle" align="right" class="tb1">Language:</td>
            <td valign="top">
				<select name="language" class="selectpicker">
				<option value="">-- select language --</option>
				<?php

					$lang_sql = "SELECT * FROM exchangerix_languages ORDER BY sort_order, language";
					$lang_result = smart_mysql_query($lang_sql);

					if (mysqli_num_rows($lang_result) > 0) {
						while ($lang_row = mysqli_fetch_array($lang_result)) {
				?>
					<option value="<?php echo $lang_row['language']; ?>" <?php if ($language == $lang_row['language']) echo 'selected="selected"'; ?>><?php echo $lang_row['language']; ?></option>

				<?php 
					}
						}
				?>
				</select>			
			</td>
          </tr>
          <tr>
            <td  width="35" valign="middle" align="right" class="tb1">Subject:</td>
            <td valign="top"><input type="text" name="esubject" id="esubject" value="<?php echo getPostParameter('esubject'); ?>" size="80" class="form-control" /></td>
          </tr>
		  <?php if (isset($email_name) && $email_name != "") { ?>
           <tr>
            <td>&nbsp;</td>
            <td height="50" bgcolor="#F7F7F7" align="center" valign="middle">
				<p>You can use following variables for this email template:</p>
				<p>
				<table width="95%" align="center" cellpadding="2" cellspacing="2" border="0">
					<?php if ($email_name == "signup") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{referral}</b></td><td  align="left"> - Referral ID</td></tr>
						<tr><td  align="right"><b>{login_url}</b></td><td  align="left"> - Login Link</td></tr>
					<?php }elseif($email_name == "activate") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{activate_link}</b></td><td  align="left"> - Activation Link</td></tr>
					<?php }elseif($email_name == "activate2") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{activate_link}</b></td><td  align="left"> - Activation Link</td></tr>
					<?php }elseif($email_name == "forgot_password") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{login_url}</b></td><td  align="left"> - Login Link</td></tr>
					<?php }elseif($email_name == "invite_friend") { ?>
						<tr><td  align="right"><b>{friend_name}</b></td><td  align="left"> - Friend First Name</td></tr>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{referral_link}</b></td><td  align="left"> - Referral Link</td></tr>
					<?php }elseif($email_name == "cashout_paid") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{amount}</b></td><td  align="left"> - Amount</td></tr>
						<tr><td  align="right"><b>{transaction_id}</b></td><td  align="left"> - Transaction ID</td></tr>
					<?php }elseif($email_name == "cashout_declined") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{transaction_id}</b></td><td  align="left"> - Transaction ID</td></tr>
						<tr><td  align="right"><b>{amount}</b></td><td  align="left"> - Amount</td></tr>
						<tr><td  align="right"><b>{reason}</b></td><td  align="left"> - Decline Reason</td></tr>
					<?php }elseif($email_name == "manual_credit") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{transaction_id}</b></td><td  align="left"> - Transaction ID</td></tr>
						<tr><td  align="right"><b>{payment_type}</b></td><td  align="left"> - Payment Type</td></tr>
						<tr><td  align="right"><b>{amount}</b></td><td  align="left"> - Amount</td></tr>
						<tr><td  align="right"><b>{status}</b></td><td  align="left"> - Transaction Status</td></tr>
					<?php }elseif($email_name == "email2users") { ?>
						<tr><td  align="right"><b>{member_id}</b></td><td  align="left"> - Member ID</td></tr>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{last_name}</b></td><td  align="left"> - Member Last Name</td></tr>
						<tr><td  align="right"><b>{unsubscribe_link}</b></td><td  align="left"> - Newsletter Unsubscribe Link</td></tr>
					<?php } ?>
				</table>
				</p>
			</td>
          </tr>
		  <?php } ?>
          <tr>
            <td  valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top">
				<textarea cols="80" id="editor" name="emessage" rows="10"><?php echo stripslashes($_POST['emessage']); ?></textarea>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor' );
				</script>		
			</td>
          </tr>
          <tr>
			<td align="center" valign="bottom">&nbsp;</td>
            <td align="left" valign="bottom">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add Email Template" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='etemplates.php'" />
            </td>
          </tr>
        </table>
      </form>

<?php require_once ("inc/footer.inc.php"); ?>