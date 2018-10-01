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

	if (isset($_POST['action']) && $_POST['action'] == "edit")
	{
		$etemplate_id	= (int)getPostParameter('eid');
		$email_name		= mysqli_real_escape_string($conn, $_POST['email_name']);
		$language		= mysqli_real_escape_string($conn, $_POST['language']);
		$email_subject	= mysqli_real_escape_string($conn, $_POST['esubject']);
		$email_message	= mysqli_real_escape_string($conn, $_POST['emessage']);

		if ($_POST['update'] && $_POST['update'] != "")
		{
			unset($errs);
			$errs = array();

			if (!($email_name && $language && $email_subject && $email_message))
			{
				$errs[] = "Please fill in all required fields";
			}
			else
			{
				$check_query = smart_mysql_query("SELECT * FROM exchangerix_email_templates WHERE template_id<>'$etemplate_id' AND language='$language' AND email_name='$email_name' AND email_name!='email2users'");
				if (mysqli_num_rows($check_query) != 0)
				{
					$errs[] = "Sorry, that email template already exists";
				}
			}

			if (count($errs) == 0)
			{
				$sql = "UPDATE exchangerix_email_templates SET language='$language', email_name='$email_name', email_subject='$email_subject', email_message='$email_message', modified=NOW() WHERE template_id='$etemplate_id' LIMIT 1";

				if (smart_mysql_query($sql))
				{
					header("Location: etemplates.php?msg=updated");
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


	if (isset($_GET['id']) && is_numeric($_GET['id'])) { $eid = (int)$_GET['id']; } else { $eid = (int)$_POST['eid']; }
	
	$query = "SELECT * FROM exchangerix_email_templates WHERE template_id='$eid' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	$title = "Edit Email Template";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {

		  $row = mysqli_fetch_array($result);
		  
      ?>

        <h2><i class="fa fa-envelope" aria-hidden="true"></i> <?php echo GetETemplateTitle($row['email_name']); ?></h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" name="form1" method="post">
          <table style="background:#F9F9F9" width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td  width="35" valign="middle" align="right" class="tb1"></td>
            <td align="right">language: <span class="badge"><?php echo $row['language']; ?></span></td>
          </tr>
          <tr>
            <td  width="35" valign="middle" align="right" class="tb1">Subject:</td>
            <td valign="top"><input type="text" name="esubject" id="esubject" value="<?php echo $row['email_subject']; ?>" size="80" class="form-control" /></td>
          </tr>
           <tr>
            <td>&nbsp;</td>
            <td height="50" bgcolor="#F7F7F7" align="center" valign="middle">
				<p>You can use following variables for this email template:</p>
				<p>
				<table width="95%" align="center" cellpadding="2" cellspacing="2" border="0">
					<?php if ($row['email_name'] == "signup") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{referral}</b></td><td  align="left"> - Referral ID</td></tr>
						<tr><td  align="right"><b>{login_url}</b></td><td  align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "activate") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{activate_link}</b></td><td  align="left"> - Activation Link</td></tr>
					<?php }elseif($row['email_name'] == "activate2") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{activate_link}</b></td><td  align="left"> - Activation Link</td></tr>
					<?php }elseif($row['email_name'] == "forgot_password") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{username}</b></td><td  align="left"> - Member Username</td></tr>
						<tr><td  align="right"><b>{password}</b></td><td  align="left"> - Member Password</td></tr>
						<tr><td  align="right"><b>{login_url}</b></td><td  align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "invite_friend") { ?>
						<tr><td  align="right"><b>{friend_name}</b></td><td  align="left"> - Friend First Name</td></tr>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{referral_link}</b></td><td  align="left"> - Referral Link</td></tr>
					<?php /*}elseif($row['email_name'] == "cashout_paid") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{transaction_id}</b></td><td  align="left"> - Transaction ID</td></tr>
						<tr><td  align="right"><b>{amount}</b></td><td  align="left"> - Amount</td></tr>
					<?php }elseif($row['email_name'] == "cashout_declined") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{transaction_id}</b></td><td  align="left"> - Transaction ID</td></tr>
						<tr><td  align="right"><b>{amount}</b></td><td  align="left"> - Amount</td></tr>
						<tr><td  align="right"><b>{reason}</b></td><td  align="left"> - Decline Reason</td></tr>
					<?php }elseif($row['email_name'] == "manual_credit") { ?>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{transaction_id}</b></td><td  align="left"> - Transaction ID</td></tr>
						<tr><td  align="right"><b>{payment_type}</b></td><td  align="left"> - Payment Type</td></tr>
						<tr><td  align="right"><b>{amount}</b></td><td  align="left"> - Amount</td></tr>
						<tr><td  align="right"><b>{status}</b></td><td  align="left"> - Transaction Status</td></tr>
					<?php */}elseif($row['email_name'] == "email2users") { ?>
						<tr><td  align="right"><b>{member_id}</b></td><td  align="left"> - Member ID</td></tr>
						<tr><td  align="right"><b>{first_name}</b></td><td  align="left"> - Member First Name</td></tr>
						<tr><td  align="right"><b>{last_name}</b></td><td  align="left"> - Member Last Name</td></tr>
						<tr><td  align="right"><b>{unsubscribe_link}</b></td><td  align="left"> - Newsletter Unsubscribe Link</td></tr>
					<?php } ?>
				</table>
				</p>
			</td>
          </tr>
          <tr>
            <td  valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top">
				<textarea cols="80" id="editor" name="emessage" rows="10"><?php echo stripslashes($row['email_message']); ?></textarea>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor' );
				</script>		
			</td>
          </tr>
			<td align="center" valign="bottom">&nbsp;</td>
            <td align="left" valign="bottom">
				<input type="hidden" name="eid" id="eid" value="<?php echo (int)$row['template_id']; ?>" />
				<input type="hidden" name="email_name" id="email_name" value="<?php echo $row['email_name']; ?>" />
				<input type="hidden" name="language" id="language" value="<?php echo $row['language']; ?>" />
				<input type="hidden" name="action" id="action" value="edit" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='etemplates.php'" />&nbsp;
				<?php if ($row['template_id'] > 8) { ?>
					<input type="button" class="btn btn-danger" name="delete" value="Delete" onclick="if (confirm('Are you sure you really want to delete this email template?') )location.href='etemplates.php?id=<?php echo $row['template_id']; ?>&action=delete';" title="Delete" />
				<?php } ?>
		  </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no email template found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>