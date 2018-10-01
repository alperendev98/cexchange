<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/iflogged.inc.php");
	require_once("inc/config.inc.php");


	if (isset($_POST['action']) && $_POST['action'] == "signup")
	{
		unset($errs);
		$errs = array();

		$fname		= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('fname'))));
		$lname		= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('lname'))));
		$email		= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$username	= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$pwd2		= mysqli_real_escape_string($conn, getPostParameter('password2'));
		//$country	= (int)getPostParameter('country');
		$country 	= 0;
		$phone		= mysqli_real_escape_string($conn, getPostParameter('phone'));
		$captcha	= mysqli_real_escape_string($conn, getPostParameter('captcha'));
		//$reg_source	= mysqli_real_escape_string($conn, getPostParameter('reg_source'));
		$newsletter	= (int)getPostParameter('newsletter');
		$tos		= (int)getPostParameter('tos');
		$ref_id		= (int)getPostParameter('referer_id');
		$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!($fname && $lname && $email && $pwd && $pwd2))  //$country
		{
			$errs[] = CBE1_SIGNUP_ERR;
		}

		if (isset($email) && $email != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = CBE1_SIGNUP_ERR4;
		}

		if (isset($pwd) && $pwd != "" && isset($pwd2) && $pwd2 != "")
		{
			if ($pwd !== $pwd2)
			{
				$errs[] = CBE1_SIGNUP_ERR6;
			}
			elseif ((strlen($pwd)) < 6 || (strlen($pwd2) < 6) || (strlen($pwd)) > 20 || (strlen($pwd2) > 20))
			{
				$errs[] = CBE1_SIGNUP_ERR7;
			}
			elseif (stristr($pwd, ' '))
			{
				$errs[] = CBE1_SIGNUP_ERR8;
			}
		}

		if (SIGNUP_CAPTCHA == 1)
		{
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

		if (!(isset($tos) && $tos == 1))
		{
			$errs[] = CBE1_SIGNUP_ERR9;
		}

		if (count($errs) == 0)
		{
				$query = "SELECT username FROM exchangerix_users WHERE username='$email' OR email='$email' LIMIT 1";
				$result = smart_mysql_query($query);

				if (mysqli_num_rows($result) != 0)
				{
					header ("Location: signup.php?msg=exists");
					exit();
				}

				// check referral
				if ($ref_id > 0)
				{
					$check_referral_query = "SELECT email FROM exchangerix_users WHERE user_id='$ref_id' LIMIT 1";
					$check_referral_result = smart_mysql_query($check_referral_query);

					if (mysqli_num_rows($check_referral_result) != 0)
						$ref_id = $ref_id;
					else
						$ref_id = 0;
				}

				$unsubscribe_key = GenerateKey($username);

				if (ACCOUNT_ACTIVATION == 1)
				{
					$activation_key = GenerateKey($username);
					$insert_query = "INSERT INTO exchangerix_users SET username='$username', password='".PasswordEncryption($pwd)."', email='$email', fname='$fname', lname='$lname', country='$country', phone='$phone', reg_source='$reg_source', ref_id='$ref_id', newsletter='$newsletter', ip='$ip', status='inactive', activation_key='$activation_key', unsubscribe_key='$unsubscribe_key', created=NOW()";
				}
				else
				{
					$insert_query = "INSERT INTO exchangerix_users SET username='$username', password='".PasswordEncryption($pwd)."', email='$email', fname='$fname', lname='$lname', country='$country', phone='$phone', reg_source='$reg_source', ref_id='$ref_id', newsletter='$newsletter', ip='$ip', status='active', activation_key='', unsubscribe_key='$unsubscribe_key', last_login=NOW(), login_count='1', last_ip='$ip', created=NOW()";
				}

				smart_mysql_query($insert_query);
				$new_user_id = mysqli_insert_id($conn);

				if (ACCOUNT_ACTIVATION == 1)
				{			
					////////////////////////////////  Send Message  //////////////////////////////
					$etemplate = GetEmailTemplate('activate');
					$esubject = $etemplate['email_subject'];
					$emessage = $etemplate['email_message'];

					$activate_link = SITE_URL."activate.php?key=".$activation_key;

					$emessage = str_replace("{first_name}", $fname, $emessage);
					$emessage = str_replace("{username}", $email, $emessage);
					$emessage = str_replace("{password}", $pwd, $emessage);
					$emessage = str_replace("{activate_link}", $activate_link, $emessage);
					$to_email = $fname.' '.$lname.' <'.$email.'>';

					SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
					////////////////////////////////////////////////////////////////////////////////

					// show activation message
					header("Location: activate.php?msg=1");
					exit();
				}
				else
				{
					////////////////////////////////  Send welcome message  ////////////////
					$etemplate = GetEmailTemplate('signup');
					$esubject = $etemplate['email_subject'];
					$emessage = $etemplate['email_message'];

					$emessage = str_replace("{first_name}", $fname, $emessage);
					$emessage = str_replace("{username}", $email, $emessage);
					$emessage = str_replace("{password}", $pwd, $emessage);
					$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
					$to_email = $fname.' '.$lname.' <'.$email.'>';

					SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
					/////////////////////////////////////////////////////////////////////////

					if (!session_id()) session_start();
					$_SESSION['userid']		= $new_user_id;
					$_SESSION['FirstName']	= $fname;

					if ($_SESSION['goto'])
					{
						$redirect_url = $_SESSION['goto'];
						unset($_SESSION['goto'], $_SESSION['goto_created']);						
					}
					else
					{
						// forward new user to account dashboard
						$redirect_url = "myaccount.php?msg=welcome";
					}
					 
					header("Location: ".$redirect_url);
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

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_SIGNUP_TITLE;
	
	require_once ("inc/header.inc.php");
	
?>

	<div class="row">
	<div class="col-md-6 col-md-offset-3">
		
		<div class="widget gray">
		
			<p class="pull-right" style="padding-top: 15px"><i class="fa fa-user"></i> <?php echo CBE1_SIGNUP_MEMBER; ?> <a href="<?php echo SITE_URL; ?>login.php"><?php echo CBE1_LOGIN_TITLE; ?></a></p>
		
			<h1><?php echo CBE1_SIGNUP_TITLE; ?></h1>

		<?php if (isset($allerrors) || isset($_GET['msg'])) { ?>
			<div class="alert alert-danger">
				<?php if (isset($_GET['msg']) && $_GET['msg'] == "exists") { ?>
					<?php echo CBE1_SIGNUP_ERR10; ?> <a href="<?php echo SITE_URL; ?>forgot.php"><?php echo CBE1_LOGIN_FORGOT; ?></a><br/>
				<?php }elseif (isset($allerrors)) { ?>
					<?php echo $allerrors; ?>
				<?php }	?>
			</div>
		<?php } ?>

        <form action="" method="post">
	        <!--<span class="req">* <?php echo CBE1_LABEL_REQUIRED; ?></span>-->
          <div class="form-group">
	          <label><?php echo CBE1_LABEL_FNAME; ?> <span class="req">* </span></label>
	          <input type="text" id="fname" class="form-control" name="fname" value="<?php echo getPostParameter('fname'); ?>">
          </div>
          <div class="form-group">
            <label><?php echo CBE1_LABEL_LNAME; ?> <span class="req">* </span></label>
            <input type="text" id="lname" class="form-control" name="lname" value="<?php echo getPostParameter('lname'); ?>">
          </div>
          <div class="form-group">
            <label><?php echo CBE1_LABEL_EMAIL2; ?> <span class="req">* </span></label>
            <input type="text" id="email" class="form-control" name="email" value="<?php echo getPostParameter('email'); ?>">
          </div>
          <div class="form-group">
            <label><?php echo CBE1_LABEL_PWD; ?> <span class="req">* </span></label> <!--<span class="note"><?php echo CBE1_SIGNUP_PTEXT; ?></span>-->
            <input type="password" id="password" class="form-control" name="password" value="">
          </div>
          <div class="form-group">
            <label><?php echo CBE1_LABEL_CPWD; ?> <span class="req">* </span></label>
            <input type="password" id="password2" class="form-control" name="password2" value="">
          </div>
          <!--
          <div class="form-group">
            <label><?php echo CBE1_LABEL_COUNTRY; ?> <span class="req">* </span></label>
				<select name="country" class="form-control" id="country">
				<option value=""><?php echo CBE1_LABEL_COUNTRY_SELECT; ?></option>
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
          </div>
          -->
          <div class="form-group">
            <label><?php echo CBE1_LABEL_PHONE; ?></label>
            <input type="text" id="phone" class="form-control" name="phone" value="<?php echo getPostParameter('phone'); ?>">
          </div>
		  <?php if (SIGNUP_CAPTCHA == 1) { ?>
          <div class="form-group">
            <label><?php echo CBE1_SIGNUP_SCODE; ?> <span class="req">* </span></label>
            <div class="row">
			   <div class="col-xs-4">
				<input type="text" id="captcha" class="form-control" name="captcha" value="">
			   </div>
			   <div class="col-xs-8">
				<img src="<?php echo SITE_URL; ?>captcha.php?rand=<?php echo rand(); ?>&bg=grey" id="captchaimg" align="absmiddle" /> <small><a href="javascript: refreshCaptcha();" title="<?php echo CBE1_SIGNUP_RIMG; ?>"><img src="<?php echo SITE_URL; ?>images/icon_refresh.png" align="absmiddle" alt="<?php echo CBE1_SIGNUP_RIMG; ?>" /></a></small>
			   </div>
          </div>
			<script language="javascript" type="text/javascript">
				function refreshCaptcha()
				{
					var img = document.images['captchaimg'];
					img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
				}
			</script>
		  <?php } ?>
		  <?php /*if (is_array($reg_sources) && count($reg_sources) > 0) { ?>
          <div class="form-group">
	          <label>&nbsp;</label>
				<select name="reg_source" class="form-control" id="reg_source">
					<option value=""><?php echo CBE1_SIGNUP_REG_SOURCE; ?></option>
					<?php foreach ($reg_sources as $v) { ?>
						<option value="<?php echo trim($v); ?>" <?php if ($reg_source == $v) echo "selected"; ?>><?php echo trim($v); ?></option>
					<?php } ?>
				</select>
          </div>
		  <?php }*/ ?>
          <div class="checkbox">
            <label><input type="checkbox" name="newsletter" class="checkboxx" value="1" <?php echo (!$_POST['action'] || @$newsletter == 1) ? "checked" : "" ?>/> <?php echo CBE1_SIGNUP_NEWSLETTER; ?></label>
          </div>
          <div class="checkbox">
            <label><input type="checkbox" name="tos" class="checkboxx" value="1" <?php echo (!$_POST['action'] || @$tos == 1) ? "checked" : "" ?>/> <?php echo CBE1_SIGNUP_AGREE; ?> <a href="<?php echo SITE_URL; ?>terms.php"><?php echo CBE1_SIGNUP_TERMS; ?></a></label>
        </div>
			<?php if (isset($_COOKIE['referer_id']) && is_numeric($_COOKIE['referer_id'])) { ?>
				<input type="hidden" name="referer_id" id="referer_id" value="<?php echo (int)$_COOKIE['referer_id']; ?>" />
			<?php } ?>
			<input type="hidden" name="action" id="action" value="signup" />
			<button type="submit" class="btn btn-success btn-lg" name="Signup" id="Signup"><b><?php echo CBE1_SIGNUP_BUTTON; ?></b></button>
        </form>
        
        </div>
        
    </div>
    </div>

<?php require_once ("inc/footer.inc.php"); ?>