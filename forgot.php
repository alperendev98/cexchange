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


	if (isset($_POST['action']) && $_POST['action'] == "forgot")
	{
		$email		= strtolower(mysqli_real_escape_string($conn, getPostParameter('email')));
		$captcha	= mysqli_real_escape_string($conn, getPostParameter('captcha'));

		if (!($email) || $email == "")
		{
			$errs[] = CBE1_FORGOT_MSG1;
		}
		else
		{
			if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				$errs[] = CBE1_FORGOT_MSG2;
			}

			if (!$captcha)
			{
				$errs[] = CBE1_SIGNUP_ERR2;
			}
			else
			{
				if (empty($_SESSION['captcha']) || strcasecmp($_SESSION['captcha'], $captcha) != 0)
				{
					$errs[] = CBE1_SIGNUP_ERR3;
				}
			}
		}

		if (count($errs) == 0)
		{
			$query = "SELECT * FROM exchangerix_users WHERE email='$email' AND status='active' LIMIT 1";
			$result = smart_mysql_query($query);

			if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_array($result);
				
				$newPassword = generatePassword(11);
				$update_query = "UPDATE exchangerix_users SET password='".PasswordEncryption($newPassword)."' WHERE user_id='".(int)$row['user_id']."' LIMIT 1";
				
				if (smart_mysql_query($update_query))
				{
					////////////////////////////////  Send Message  //////////////////////////////
					$etemplate = GetEmailTemplate('forgot_password');
					$esubject = $etemplate['email_subject'];
					$emessage = $etemplate['email_message'];

					$emessage = str_replace("{first_name}", $row['fname'], $emessage);
					$emessage = str_replace("{username}", $row['username'], $emessage);
					$emessage = str_replace("{password}", $newPassword, $emessage);
					$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);	
					$to_email = $row['fname'].' '.$row['lname'].' <'.$email.'>';

					SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
					
					header("Location: forgot.php?msg=sent");
					exit();
					///////////////////////////////////////////////////////////////////////////////
				}
			}
			else
			{
				header("Location: forgot.php?msg=3");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>\n";
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_FORGOT_TITLE;

	require_once "inc/header.inc.php";
	
?>

	<h1><?php echo CBE1_FORGOT_TITLE; ?></h1>

	<div class="row">
	<div class="col-md-6 col-md-offset-3">

	<?php if (isset($allerrors) || (isset($_GET['msg']) && is_numeric($_GET['msg']) && $_GET['msg'] != "sent")) { ?>
		<div class="alert alert-danger">
			<?php if ($_GET['msg'] == 3) { echo CBE1_FORGOT_MSG3; }elseif (isset($allerrors)) { echo $allerrors; } ?>
		</div>
	<?php }elseif($_GET['msg'] == "sent"){ ?>
		<div class="alert alert-success"><?php echo CBE1_FORGOT_MSG4; ?></div>
		<p align="center"><a class="goback" href="<?php echo SITE_URL; ?>login.php"><?php echo CBE1_FORGOT_GOBACK; ?></a></p>
	<?php }else{ ?> 
		<p align="center"><?php echo CBE1_FORGOT_TEXT; ?></p>
	<?php } ?>

	<?php if (!(isset($_GET['msg']) && $_GET['msg'] == "sent")) { ?>
	<div class="widget gray">
		
      <form action="" method="post">
        <div class="form-group">
            <label><?php echo CBE1_FORGOT_EMAIL; ?></label>
            <input type="email" class="form-control" name="email" required="required" value="<?php echo getPostParameter('email'); ?>" />
          </div>
          <div class="form-group row">
	          <div class="col-xs-6">
	            <label><?php echo CBE1_SIGNUP_SCODE; ?></label>
				<input type="text" id="captcha" class="form-control" name="captcha" value="">
	          </div>
	          <div class="col-xs-6" style="padding-top: 25px;">
				<img src="<?php echo SITE_URL; ?>captcha.php?rand=<?php echo rand(); ?>&bg=grey" id="captchaimg" align="absmiddle" /> <small><a href="javascript: refreshCaptcha();" title="<?php echo CBE1_SIGNUP_RIMG; ?>"><img src="<?php echo SITE_URL; ?>images/icon_refresh.png" align="absmiddle" alt="<?php echo CBE1_SIGNUP_RIMG; ?>" /></a></small>
	          </div>
          </div>          
			<script language="javascript" type="text/javascript">
				function refreshCaptcha()
				{
					var img = document.images['captchaimg'];
					img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000+"&bg=grey";
				}
			</script>
          <div class="form-group">
		  	<input type="hidden" name="action" value="forgot" />
			<button type="submit" class="btn btn-success btn-lg" name="send" id="send"><?php echo CBE1_FORGOT_BUTTON; ?></button>
          </div>
      </form>
      
	</div>
	<?php } ?>
	
	</div>
	</div>
	

<?php require_once ("inc/footer.inc.php"); ?>