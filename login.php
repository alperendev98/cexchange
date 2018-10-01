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


	if (isset($_POST['action']) && $_POST['action'] == "login")
	{
		$username	= mysqli_real_escape_string($conn, getPostParameter('username'));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$remember	= (int)getPostParameter('rememberme');
		$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!($username && $pwd))
		{
			$errormsg = CBE1_LOGIN_ERR;
		}
		else
		{
			$sql = "SELECT * FROM exchangerix_users WHERE username='$username' AND password='".PasswordEncryption($pwd)."' LIMIT 1";
			$result = smart_mysql_query($sql);

			if (mysqli_num_rows($result) != 0)
			{
					$row = mysqli_fetch_array($result);

					if ($row['status'] == 'inactive')
					{
						header("Location: login.php?msg=2");
						exit();
					}

					if (LOGIN_ATTEMPTS_LIMIT == 1)
					{
						unset($_SESSION['attems_'.$username."_".$ip], $_SESSION['attems_left']);
					}

					if ($remember == 1)
					{
						$cookie_hash = md5(sha1($username.$ip));
						setcookie("usname", $cookie_hash, time()+3600*24*365, '/');
						$login_sql = "login_session = '$cookie_hash', ";
					}

					smart_mysql_query("UPDATE exchangerix_users SET ".$login_sql." last_ip='$ip', login_count=login_count+1, last_login=NOW() WHERE user_id='".(int)$row['user_id']."' LIMIT 1");

					if (!session_id()) session_start();
					$_SESSION['userid']		= $row['user_id'];
					$_SESSION['FirstName']	= $row['fname'];
					$_SESSION['Email']		= $row['email'];

					if ($_SESSION['goto'])
					{
						$redirect_url = $_SESSION['goto'];
						unset($_SESSION['goto'], $_SESSION['goto_created']);
					}
					else
					{
						$redirect_url = "myaccount.php";
					}

					header("Location: ".$redirect_url);
					exit();
			}
			else
			{
				if (LOGIN_ATTEMPTS_LIMIT == 1)
				{
					$check_sql = "SELECT * FROM exchangerix_users WHERE username='$username' AND status!='inactive' AND block_reason!='login attempts limit' LIMIT 1";
					$check_result = smart_mysql_query($check_sql);

					if (mysqli_num_rows($check_result) != 0)
					{
						if (!session_id()) session_start();
						$_SESSION['attems_'.$username."_".$ip] += 1;
						$_SESSION['attems_left'] = LOGIN_ATTEMPTS - $_SESSION['attems_'.$username.'_'.$ip];

						if ($_SESSION['attems_left'] == 0)
						{ 
							// block user //
							smart_mysql_query("UPDATE exchangerix_users SET status='inactive', block_reason='login attempts limit' WHERE username='$username' LIMIT 1"); 
							unset($_SESSION['attems_'.$username."_".$ip], $_SESSION['attems_left']);
					
							header("Location: login.php?msg=6");
							exit();
						}
						else
						{
							header("Location: login.php?msg=5");
							exit();
						}
					}
				}

				header("Location: login.php?msg=1");
				exit();
			}
		}
	}
	

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_LOGIN_TITLE;

	require_once ("inc/header.inc.php");

?>

	<div class="row">
	<div class="col-md-6 col-md-offset-3">

			<div class="widget gray">
			
			<h1><?php echo CBE1_LOGIN_TITLE; ?></h1>

			<?php if (isset($errormsg) || isset($_GET['msg'])) { ?>
				<div class="alert alert-danger">
					<?php if (isset($errormsg) && $errormsg != "") { ?>
						<?php echo $errormsg; ?>
					<?php }else{ ?>
						<?php if ($_GET['msg'] == 1) { echo CBE1_LOGIN_ERR1; } ?>
						<?php if ($_GET['msg'] == 2) { echo CBE1_LOGIN_ERR2; } ?>
						<?php if ($_GET['msg'] == 3) { echo CBE1_LOGIN_ERR3; } ?>
						<?php if ($_GET['msg'] == 4) { echo CBE1_LOGIN_ERR4; } ?>
						<?php if ($_GET['msg'] == 5) { echo CBE1_LOGIN_ERR1." ".(int)$_SESSION['attems_left']." ".CBE1_LOGIN_ATTEMPTS; } ?>
						<?php if ($_GET['msg'] == 6) { echo CBE1_LOGIN_ERR6; } ?>
					<?php } ?>
				</div>
			<?php } ?>
			
			<form action="" method="post">	
               <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                  <input id="login-username" type="text" class="form-control input-lg" name="username" value="<?php echo getPostParameter('username'); ?>" placeholder="<?php echo CBE1_LOGIN_EMAIL; ?>" required="required">                                        
               </div>
               <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                  <input id="login-password" type="password" class="form-control input-lg" name="password" value="" placeholder="<?php echo CBE1_LOGIN_PASSWORD; ?>">
               </div>			  
			  <div class="checkbox">
				<label><input type="checkbox" class="checkboxx" name="rememberme" id="rememberme" value="1" checked="checked" /> <?php echo CBE1_LOGIN_REMEMBER; ?></label>
			  </div>
				<input type="hidden" name="action" value="login" />
				<button type="submit" class="btn btn-success btn-lg" name="login" id="login"><?php echo CBE1_LOGIN_BUTTON; ?></button>
				<br/><br/><p><a href="<?php echo SITE_URL; ?>forgot.php"><?php echo CBE1_LOGIN_FORGOT; ?></a></p>
				<?php if (ACCOUNT_ACTIVATION == 1) { ?>
					<p><a href="<?php echo SITE_URL; ?>activation_email.php"><?php echo CBE1_LOGIN_AEMAIL; ?></a></p>
				<?php } ?>
				<p><?php echo CBE1_LOGIN_NMEMBER; ?> <a href="<?php echo SITE_URL; ?>signup.php"><?php echo CBE_SIGNUP; ?></a></p>
		  	</form>
		  	</div>
		  	
		  	<!-- //facebook login //dev-->

	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>