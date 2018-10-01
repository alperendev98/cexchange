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

	$cpage = 24;

	CheckAdminPermissions($cpage);

	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$username = (int)$_REQUEST['id'];
		$uresult = smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_id='$username' LIMIT 1");
		if (mysqli_num_rows($uresult) != 0) $urow = mysqli_fetch_array($uresult);
	}

	if (isset($_POST['etemplate']) && is_numeric($_POST['etemplate']) && !$_POST['send'])
	{
		$etemplate_id = (int)$_POST['etemplate'];
		$tres = smart_mysql_query("SELECT * FROM exchangerix_email_templates WHERE template_id='$etemplate_id' AND email_name='email2users' LIMIT 1");
		if (mysqli_num_rows($tres) != 0)
		{
			$trow = mysqli_fetch_array($tres);
			$subject = $trow['email_subject'];
			$allmessage = $trow['email_message'];
		}
	}

	$unsubscribe_msg = "
		<br/><br/><br/>
		<p style='font-family:arial,helvetica,tahoma,verdana,sans-serif;font-size:12px;color:#5B5B5B;text-align:left;padding-top:12px;'>	
		--------------------------------------------------------------------------------------------<br/>
		You are receiving this email as you have directly signed up to ".SITE_TITLE.".<br/>If you do not wish to receive these messages in the future, please <a href='{unsubscribe_link}' target='_blank'>unsubscribe</a>.</p>";

	$query = "SELECT * FROM exchangerix_users WHERE email != '' AND newsletter='1' AND status='active'";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$query2 = "SELECT * FROM exchangerix_users WHERE email != ''";
	$result2 = smart_mysql_query($query2);
	$total2 = mysqli_num_rows($result2);


	if (isset($_POST['send']) && $_POST['send'] != "")
	{
		$etemplate	= 0;
		$recipients = $_POST['recipients'];
		$allmessage = $_POST['allmessage'];
		if (!$username) $username = mysqli_real_escape_string($conn, getPostParameter('username'));

		unset($errs);
		$errs = array();

		if (!($_POST['msubject'] && $_POST['allmessage']))
		{
			$errs[] = "Please enter subject and message";
		}
		else
		{
			switch ($recipients)
			{
				case "all":			$query = "SELECT * FROM exchangerix_users WHERE email != ''"; break;
				case "subscribed":	$query = "SELECT * FROM exchangerix_users WHERE email != '' AND newsletter='1' AND status='active'"; break;
				case "member":		$query = "SELECT * FROM exchangerix_users WHERE user_id='$username' LIMIT 1"; break;
			}

			$result = smart_mysql_query($query);
			
			if (mysqli_num_rows($result) == 0)
			{
				$errs[] = "Member not found";
			}
		}

		if (count($errs) == 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				$msubject	= trim($_POST['msubject']);
				$allmessage = $_POST['allmessage'];

				////////////////////////////////  Send Message  //////////////////////////////
				$msubject	= str_replace("{first_name}", $row['fname'], $msubject);
				$allmessage = str_replace("{member_id}", $row['user_id'], $allmessage);
				$allmessage = str_replace("{first_name}", $row['fname'], $allmessage);
				$allmessage = str_replace("{last_name}", $row['lname'], $allmessage);
				$allmessage = str_replace("{unsubscribe_link}", SITE_URL."unsubscribe.php?key=".$row['unsubscribe_key'], $allmessage);
				$message = "<html>
							<head>
								<title>".$subject."</title>
							</head>
							<body>".$allmessage."</body>
							</html>";
				$to_email = $row['fname'].' '.$row['lname'].' <'.$row['email'].'>';

				SendEmail($to_email, $msubject, $message, $noreply_mail = 1);
				///////////////////////////////////////////////////////////////////////////////
			}

			header ("Location: email2users.php?msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	$title = "Send Email";
	require_once ("inc/header.inc.php");

?>

      <?php if ($total2 > 0) { ?>

        <h2><i class="fa fa-paper-plane" aria-hidden="true"></i> Send Email</h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
			<div class="alert alert-success">Your message has been successfully sent!</div>
		<?php } ?>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<script type="text/javascript">
		function send_to(){
			recipient = $("#recipients").val();
			if(recipient == "member"){
				$("#single_member").show();
			}else{
				$("#single_member").hide();
			}
		}
		</script>

		<?php if (!@$username) { ?>
		<div class="subscribers">
			<span style="font-size:15px; color:#FFF; background:#777777; padding:3px 8px; border-radius: 5px;"><?php echo $total2; ?></span>&nbsp; <?php echo ($total2 == 1) ? "member" : "members"; ?><br/><br/>
			<span style="font-size:15px; color:#FFF; background:#6b99ba; padding:3px 8px; border-radius: 5px;"><?php echo $total; ?></span>&nbsp; subscribed <?php echo ($total == 1) ? "member" : "members"; ?>
		</div>
		<?php } ?>

        <form id="form1" name="form1" action="email2users.php" method="post">
          <table width="100%" style="background:#F9F9F9" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td  valign="middle" align="left" class="tb1">From:</td>
            <td valign="middle"><b><?php echo EMAIL_FROM_NAME; ?></b> &lt;<?php echo NOREPLY_MAIL; ?>&gt; &nbsp; <a href="settings.php#mail"><img src="images/icon_edit.png" align="absmiddle" />edit</a></td>
          </tr>
		  <?php if ($urow['email'] == "") { ?>
          <tr>
            <td width="35" valign="middle" align="left" class="tb1">Send To:</td>
            <td valign="middle">
				<select name="recipients" id="recipients" onchange="send_to();" class="selectpicker">
					<option value="all" <?php echo ($recipients == 'all') ? "selected='selected'" : ""; ?>>All Members (<?php echo $total2; ?>)</option>
					<?php if ($total > 0) { ?>
						<option value="subscribed" <?php echo ($recipients == 'subscribed') ? "selected='selected'" : ""; ?>>Subscribed Members (<?php echo $total; ?>)</option>
					<?php } ?>
					<option value="member" <?php echo ($recipients == 'member' || $_GET['id']) ? "selected='selected'" : ""; ?>>A single member</option>
				</select>
			</td>
          </tr>
		  <?php } ?>
		  <tr id="single_member" <?php if (@$recipients != "member" && !$_REQUEST['id']) { ?>style="display:none;"<?php } ?>>
            <td valign="middle" align="left" class="tb1"><?php if ($urow['email'] == "") { ?>User ID:<?php }else{ ?>To:<?php } ?></td>
            <td valign="middle">
				<?php if ($urow['email'] == "") { ?>
					<input type="text" class="form-control" name="username" id="username" value="<?php echo @$username; ?>" size="10" />
				<?php }else{ ?>
					<b><?php echo $urow['fname']." ".$urow['lname']; ?></b> &lt;<?php echo $urow['email']; ?>&gt;
				<?php } ?>
			</td>
		  </tr>
		  <?php
				$e_sql = "SELECT * FROM exchangerix_email_templates WHERE email_name='email2users' ORDER BY template_id";
				$e_result = smart_mysql_query($e_sql);
				if (mysqli_num_rows($e_result) > 0) {
		  ?>
          <tr>
            <td  valign="middle" align="left" class="tb1">Template:</td>
            <td valign="top">
				<select name="etemplate" id="etemplate" onchange="this.form.submit();" style="width: 150px;" class="selectpicker">
					<option value="">------------</option>
					<?php while ($e_row = mysqli_fetch_array($e_result)) { ?>
						<option value="<?php echo $e_row['template_id']; ?>" <?php if ($_POST['etemplate'] == $e_row['template_id']) echo 'selected="selected"'; ?>><?php echo substr($e_row['email_subject'],0,80); ?></option>
					<?php } ?>
				</select>
			</td>
          </tr>
		  <?php } ?>
          <tr>
            <td  valign="middle" align="left" class="tb1">Subject:</td>
            <td valign="top"><input type="text" name="msubject" id="msubject" value="<?php echo ($subject) ? $subject : getPostParameter('msubject'); ?>" size="80" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td bgcolor="#F5F5F5" align="center" valign="middle">
				<p>Your message can use the following variables:</p>
				<p><b>{member_id}</b> - Member ID, <b>{first_name}</b> - First Name, <b>{last_name}</b> - Last Name, <b>{unsubscribe_link}</b> - Unsubscribe Link</p><br/>
			</td>
          </tr>
          <tr>
            <td  valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top">
				<textarea cols="80" id="editor" name="allmessage" rows="10"><?php echo ($allmessage) ? stripslashes($allmessage) : $unsubscribe_msg; ?></textarea>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor' );
				</script>		
			</td>
          </tr>
          <tr>
			<td align="center" valign="top">&nbsp;</td>
			<td height="45" align="left" valign="middle">
				<?php if ($username) { ?><input type="hidden" name="id" value="<?php echo $username; ?>" /><?php } ?>
				<input type="hidden" name="action" id="action" value="email2users" />
				<input type="submit" name="send" id="send" class="btn btn-success" value="Send Message" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='index.php'" />
            </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
			<h2><i class="fa fa-paper-plane" aria-hidden="true"></i> Send Email</h2>
			<div class="alert alert-info">There are no members at this time.</div>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>